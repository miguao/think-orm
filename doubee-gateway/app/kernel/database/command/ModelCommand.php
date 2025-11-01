<?php

declare(strict_types=1);

namespace app\kernel\database\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\App;
use think\facade\Config;
use think\facade\Db;

class ModelCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('gen:model');
        $this->addArgument('table', Argument::OPTIONAL, 'Which table you want to associated with the Model.');
        $this->addOption('force', 'f', Option::VALUE_NONE, 'Overwrite existing file');
    }

    protected function execute(Input $input, Output $output): void
    {
        $tableArg = trim((string)$input->getArgument('table'));
        if ($tableArg === '') {
            $output->writeln('<error>Please provide a table name. Usage: php think gen:model table_name</error>');
            return;
        }

        $force = (bool)$input->getOption('force');

        // 默认命名空间与目标目录
        $targetNamespace = 'app\\model';
        $appPath = App::getAppPath();
        $targetDir = rtrim($appPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'model';

        $className = $this->tableToClassName($tableArg);

        // 模板文件（固定位置）
        $stubFile = __DIR__ . '/stubs/Model.stub';
        if (!is_file($stubFile)) {
            $output->writeln('<error>Stub file not found: ' . $stubFile . '</error>');
            return;
        }
        $stub = file_get_contents($stubFile);
        if ($stub === false) {
            $output->writeln('<error>Failed to read stub file: ' . $stubFile . '</error>');
            return;
        }

        // 读取表前缀（兼容常见配置）
        $prefix = '';
        try {
            $connConfig = Config::get('database.connections.mysql');
            if (is_array($connConfig) && !empty($connConfig['prefix'])) {
                $prefix = (string)$connConfig['prefix'];
            } else {
                $prefix = (string)Config::get('database.prefix', '');
            }
        } catch (\Throwable $e) {
            $prefix = (string)Config::get('database.prefix', '');
        }

        // 决定实际表名（优先尝试带前缀）
        try {
            $candidates = [$tableArg];
            if ($prefix !== '' && !str_starts_with($tableArg, $prefix)) {
                $candidates[] = $prefix . $tableArg;
            }

            $foundTable = null;
            foreach ($candidates as $candidate) {
                $exists = Db::query(
                    'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME = ?',
                    [$candidate]
                );
                if (!empty($exists)) {
                    $foundTable = (string)($exists[0]['TABLE_NAME'] ?? $exists[0]['table_name']);
                    break;
                }
            }

            if ($foundTable === null) {
                // 模糊匹配（尝试找到最接近的表）
                $like = '%' . str_replace('%', '\\%', $tableArg) . '%';
                $matches = Db::query(
                    'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME LIKE ? LIMIT 1',
                    [$like]
                );
                if (!empty($matches)) {
                    $foundTable = (string)($matches[0]['TABLE_NAME'] ?? $matches[0]['table_name']);
                    $output->writeln('<comment>Using matched table name: ' . $foundTable . '</comment>');
                } else {
                    $output->writeln('<error>Table not found: ' . $tableArg . '</error>');
                    return;
                }
            }

            // 获取列信息（按列顺序）
            $rows = Db::query(
                'SELECT COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
                [$foundTable]
            );
        } catch (\Throwable $e) {
            $output->writeln('<error>Failed to fetch table columns: ' . $e->getMessage() . '</error>');
            return;
        }

        if (empty($rows)) {
            $output->writeln('<error>No columns found for table: ' . ($foundTable ?? $tableArg) . '</error>');
            return;
        }

        // 生成 @property 注释块
        $props = [];
        foreach ($rows as $col) {
            $dbType = (string)($col['DATA_TYPE'] ?? $col['data_type'] ?? 'string');
            $phpType = $this->dbTypeToPhpType($dbType);
            $colName = (string)($col['COLUMN_NAME'] ?? $col['column_name']);
//            $comment = trim((string)($col['COLUMN_COMMENT'] ?? $col['column_comment'] ?? ''));
            $line = ' * @property ' . $phpType . ' $' . $colName;
//            if ($comment !== '') {
//                $line .= ' ' . $comment;
//            }
            $props[] = $line;
        }

        $propsBlock = implode(PHP_EOL, $props);

        $usesBlock = "use think\\Model;";
        $commentBlock = "/**\n{$propsBlock}\n */";
        $replacements = [
            '%NAMESPACE%' => $targetNamespace,
            '%USES%' => $usesBlock,
            '%CLASS%' => $className,
            '%INHERITANCE%' => 'Model',
        ];

        $content = strtr($stub, $replacements);

        $content = preg_replace(
            '/(class\s+' . preg_quote($className, '/') . '\s+(extends|implements|{))/i',
            $commentBlock . "\n$0",
            $content,
            1
        );

        // 写入文件
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            $output->writeln('<error>Failed to create target directory: ' . $targetDir . '</error>');
            return;
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $className . '.php';
        if (file_exists($filePath) && !$force) {
            $output->writeln('<error>File already exists: ' . $filePath . '. Use --force to overwrite.</error>');
            return;
        }

        if (file_put_contents($filePath, $content) === false) {
            $output->writeln('<error>Failed to write file: ' . $filePath . '</error>');
            return;
        }

        $output->writeln(sprintf('<info>Model %s was created at %s</info>', $className, $filePath));
    }

    private function tableToClassName(string $table): string
    {
        $parts = preg_split('/[_\-]+/', $table);
        $parts = array_filter($parts, static fn($p) => $p !== '');
        $parts = array_map(static fn($p) => ucfirst($p), $parts);
        return implode('', $parts);
    }

    protected function dbTypeToPhpType(string $dbType): string
    {
        $dbType = strtolower($dbType);
        if (in_array($dbType, ['int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'year'], true)) {
            return 'int';
        }
        if (in_array($dbType, ['float', 'double', 'decimal'], true)) {
            return 'float';
        }
        if ($dbType === 'json') {
            return 'array';
        }
        if (in_array($dbType, ['datetime', 'timestamp', 'date', 'time'], true)) {
            return 'string';
        }
        return 'string';
    }
}