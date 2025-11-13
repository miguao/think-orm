<?php

declare(strict_types=1);

/**
 * 验证并规范化硬件ID
 * @param string|null $hardwareIdSpecified 用户指定的硬件ID
 * @return string|null 规范化后的硬件ID，无效返回null
 */
function validate_and_normalize_hardware_id(?string $hardwareIdSpecified): ?string
{
    if ($hardwareIdSpecified === null || strlen($hardwareIdSpecified) < 8) {
        return null;
    }

    if (!preg_match('/^[0-9A-Fa-f]+$/', $hardwareIdSpecified)) {
        return null;
    }

    return strtoupper(substr(str_pad($hardwareIdSpecified, 16, '0'), 0, 16));
}

/**
 * 验证MAC地址是否有效
 * @param string $macAddress MAC地址（已去除分隔符）
 * @return bool 是否有效
 */
function is_valid_mac_address(string $macAddress): bool
{
    return strlen($macAddress) === 12
        && preg_match('/^[0-9A-Fa-f]{12}$/', $macAddress)
        && $macAddress !== '000000000000';
}

/**
 * 验证序列号是否有效（排除默认值）
 * @param string $serialNumber 序列号
 * @return bool 是否有效
 */
function is_valid_serial_number(string $serialNumber): bool
{
    $normalized = strtoupper(trim($serialNumber));
    return !empty($normalized) && $normalized !== 'TO BE FILLED BY O.E.M.';
}

/**
 * 从WMIC命令输出中提取值
 * @param string|null $output WMIC命令输出
 * @param string $pattern 正则表达式模式
 * @return string|null 提取的值，失败返回null
 */
function extract_wmic_value(?string $output, string $pattern): ?string
{
    if ($output === null || !preg_match($pattern, $output, $matches)) {
        return null;
    }

    return trim($matches[1]);
}

/**
 * 从shell命令输出中提取MAC地址
 * @param string|null $output shell命令输出
 * @param string $pattern 正则表达式模式
 * @return string|null 提取的MAC地址（已去除分隔符），失败返回null
 */
function extract_mac_address(?string $output, string $pattern): ?string
{
    if ($output === null || !preg_match($pattern, $output, $matches)) {
        return null;
    }

    $macAddress = str_replace([':', '-'], '', strtoupper(trim($matches[1])));
    return is_valid_mac_address($macAddress) ? $macAddress : null;
}

/**
 * 获取Windows系统的硬件信息
 * @return array<string> 硬件信息数组
 */
function get_windows_hardware_info(): array
{
    $hardwareInfo = [];

    // 方法1: 获取物理网卡MAC地址（排除虚拟网卡）
    $output = @shell_exec('getmac /fo csv /nh /v 2>nul');
    if ($output) {
        foreach (explode("\n", trim($output)) as $line) {
            $parts = str_getcsv(trim($line));
            if (count($parts) >= 4) {
                $macAddress = str_replace(['-', ':'], '', trim($parts[0]));
                $transport = strtolower(trim($parts[3] ?? ''));

                if (
                    is_valid_mac_address($macAddress)
                    && !str_contains($transport, 'media disconnected')
                ) {
                    $hardwareInfo[] = 'mac:' . strtoupper($macAddress);
                    break;
                }
            }
        }
    }

    // 方法2: 获取CPU序列号
    $output = @shell_exec('wmic cpu get ProcessorId /value 2>nul');
    $cpuIdentifier = extract_wmic_value($output, '/ProcessorId=([^\r\n]+)/i');
    if ($cpuIdentifier && $cpuIdentifier !== 'FFFFFFFF-FFFF-FFFF-FFFF-FFFFFFFFFFFF') {
        $hardwareInfo[] = 'cpu:' . $cpuIdentifier;
    }

    // 方法3: 获取主板序列号
    $output = @shell_exec('wmic baseboard get serialnumber /value 2>nul');
    $motherboardSerialNumber = extract_wmic_value($output, '/SerialNumber=([^\r\n]+)/i');
    if ($motherboardSerialNumber && is_valid_serial_number($motherboardSerialNumber)) {
        $hardwareInfo[] = 'mb:' . $motherboardSerialNumber;
    }

    // 方法4: 获取硬盘序列号（系统盘）
    $output = @shell_exec('wmic diskdrive where "index=0" get serialnumber /value 2>nul');
    $diskSerialNumber = extract_wmic_value($output, '/SerialNumber=([^\r\n]+)/i');
    if ($diskSerialNumber) {
        $hardwareInfo[] = 'disk:' . $diskSerialNumber;
    }

    // 方法5: 获取BIOS序列号
    $output = @shell_exec('wmic bios get serialnumber /value 2>nul');
    $biosSerialNumber = extract_wmic_value($output, '/SerialNumber=([^\r\n]+)/i');
    if ($biosSerialNumber && is_valid_serial_number($biosSerialNumber)) {
        $hardwareInfo[] = 'bios:' . $biosSerialNumber;
    }

    return $hardwareInfo;
}

/**
 * 获取Linux系统的硬件信息
 * @return array<string> 硬件信息数组
 */
function get_linux_hardware_info(): array
{
    $hardwareInfo = [];

    // 方法1: 获取物理网卡MAC地址（排除虚拟网卡）
    // 优化：只执行一次 ip link show 命令，在PHP中解析
    $ipLinkOutput = @shell_exec('ip link show 2>/dev/null');
    if ($ipLinkOutput) {
        $lines = explode("\n", $ipLinkOutput);
        foreach ($lines as $i => $line) {
            // 查找接口行（格式：1: eth0: ...）
            if (preg_match('/^\d+:\s+([^:]+):/', $line, $matches)) {
                $interface = trim($matches[1]);
                // 跳过lo接口
                if ($interface === 'lo') {
                    continue;
                }
                // 检查下一行是否有MAC地址
                if (isset($lines[$i + 1]) && preg_match('/link\/ether\s+([0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2})/i', $lines[$i + 1], $macMatches)) {
                    $macAddress = extract_mac_address($lines[$i + 1], '/([0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2})/i');
                    if ($macAddress) {
                        $hardwareInfo[] = 'mac:' . $macAddress;
                        break; // 找到第一个有效MAC后退出
                    }
                }
            }
        }
    }

    // 备用方法：使用 ifconfig
    if (empty($hardwareInfo)) {
        $output = @shell_exec("ifconfig 2>/dev/null | grep -E '^[a-z0-9]+' | grep -v 'lo:' | head -1");
        if ($output) {
            $interface = preg_split('/\s+/', trim($output))[0] ?? '';
            if ($interface) {
                $output2 = @shell_exec("ifconfig {$interface} 2>/dev/null | grep -oE '([0-9a-f]{2}:){5}[0-9a-f]{2}' | head -1");
                $macAddress = extract_mac_address($output2, '/([0-9a-f]{2}:){5}[0-9a-f]{2}/i');
                if ($macAddress) {
                    $hardwareInfo[] = 'mac:' . $macAddress;
                }
            }
        }
    }

    // 方法2: 获取CPU ID（优化：支持更多格式，即使部分信息缺失也能生成标识）
    $cpuInfo = @file_get_contents('/proc/cpuinfo');
    if ($cpuInfo && strlen($cpuInfo) > 10) {
        // 尝试获取 CPU serial number (ARM架构)
        $cpuSerialNumber = extract_wmic_value($cpuInfo, '/^Serial\s*:\s*([0-9a-f]+)$/im');
        if ($cpuSerialNumber) {
            $hardwareInfo[] = 'cpu:' . strtoupper($cpuSerialNumber);
        } else {
            // 获取 CPU 型号和特性组合（支持多种格式）
            $cpuModelName = extract_wmic_value($cpuInfo, '/^model name\s*:\s*(.+)$/im');
            if (!$cpuModelName) {
                $cpuModelName = extract_wmic_value($cpuInfo, '/^Hardware\s*:\s*(.+)$/im');
            }
            if (!$cpuModelName) {
                $cpuModelName = extract_wmic_value($cpuInfo, '/^Processor\s*:\s*(.+)$/im');
            }
            if (!$cpuModelName) {
                $cpuModelName = extract_wmic_value($cpuInfo, '/^cpu\s*:\s*(.+)$/im');
            }

            $processorNumber = extract_wmic_value($cpuInfo, '/^processor\s*:\s*(\d+)$/im');
            if (!$processorNumber) {
                $bogoMips = extract_wmic_value($cpuInfo, '/^BogoMIPS\s*:\s*([0-9.]+)$/im');
                if ($bogoMips) {
                    $processorNumber = md5($bogoMips);
                }
            }

            // 如果已有CPU型号或处理器号，直接使用；否则收集关键信息组合
            if ($cpuModelName && $processorNumber) {
                $hardwareInfo[] = 'cpu:' . md5($cpuModelName . $processorNumber);
            } elseif ($cpuModelName) {
                $hardwareInfo[] = 'cpu:' . md5($cpuModelName);
            } elseif ($processorNumber) {
                $hardwareInfo[] = 'cpu:' . md5('processor' . $processorNumber);
            } else {
                // 最后备用：使用CPU信息的前20行关键信息组合
                $cpuLines = explode("\n", $cpuInfo);
                $cpuKeyInfo = [];
                foreach (array_slice($cpuLines, 0, 20) as $line) {
                    $line = trim($line);
                    if (preg_match('/^([a-zA-Z\s]+):\s*(.+)$/', $line, $matches)) {
                        $key = strtolower(trim($matches[1]));
                        $value = trim($matches[2]);
                        // 只收集关键字段
                        if (in_array($key, ['processor', 'model name', 'hardware', 'cpu', 'bogomips', 'cpu implementer', 'cpu architecture', 'cpu variant', 'cpu part'])) {
                            $cpuKeyInfo[] = $key . ':' . $value;
                        }
                    }
                }
                if (!empty($cpuKeyInfo)) {
                    $hardwareInfo[] = 'cpu:' . md5(implode('|', $cpuKeyInfo));
                }
            }
        }
    }

    // 方法3: 获取主板序列号（DMI）- 多种方法（在虚拟化环境中可能不可用）
    $output = @shell_exec('cat /sys/class/dmi/id/board_serial 2>/dev/null');
    $motherboardSerialNumber = $output ? trim($output) : null;
    if ($motherboardSerialNumber && is_valid_serial_number($motherboardSerialNumber)) {
        $hardwareInfo[] = 'mb:' . $motherboardSerialNumber;
    } else {
        // 备用方法1: 尝试获取主板产品名称
        $output = @shell_exec('cat /sys/class/dmi/id/board_name 2>/dev/null');
        $boardName = $output ? trim($output) : null;
        if (!empty($boardName) && strtoupper($boardName) !== 'TO BE FILLED BY O.E.M.') {
            $hardwareInfo[] = 'mb:' . md5($boardName);
        } else {
            // 备用方法2: 尝试获取主板厂商
            $output = @shell_exec('cat /sys/class/dmi/id/board_vendor 2>/dev/null');
            $boardVendor = $output ? trim($output) : null;
            if (!empty($boardVendor)) {
                $hardwareInfo[] = 'mb:' . md5($boardVendor);
            }
        }
    }

    // 方法4: 获取硬盘序列号
    $output = @shell_exec("lsblk -d -o serial 2>/dev/null | grep -v '^SERIAL' | head -1");
    $diskSerialNumber = $output ? trim($output) : null;
    if ($diskSerialNumber) {
        $hardwareInfo[] = 'disk:' . $diskSerialNumber;
    }

    // 备用方法：从 /dev/disk/by-id/ 获取
    $hasDiskInfo = !empty(array_filter($hardwareInfo, fn($item) => str_starts_with($item, 'disk:')));
    if (!$hasDiskInfo) {
        $output = @shell_exec("ls -la /dev/disk/by-id/ 2>/dev/null | grep -E 'ata-|nvme-' | head -1");
        if ($output && preg_match('/ata-([^\s]+)|nvme-([^\s]+)/', $output, $matches)) {
            $diskIdentifier = $matches[1] ?? $matches[2] ?? null;
            if ($diskIdentifier) {
                $hardwareInfo[] = 'disk:' . $diskIdentifier;
            }
        }
    }

    // 方法5: 获取机器ID（systemd）- 在容器/虚拟化环境中可能不可用
    $machineIdentifier = @file_get_contents('/etc/machine-id');
    if ($machineIdentifier) {
        $machineIdentifier = trim($machineIdentifier);
        if (strlen($machineIdentifier) >= 16) {
            $hardwareInfo[] = 'machine:' . substr($machineIdentifier, 0, 32);
        }
    }

    // 方法6: 获取系统UUID（DMI）- 在虚拟化环境中可能不可用
    // 检查是否已有UUID（避免重复）
    $hasUuid = false;
    foreach ($hardwareInfo as $item) {
        if (str_starts_with($item, 'uuid:')) {
            $hasUuid = true;
            break;
        }
    }
    if (!$hasUuid) {
        $output = @shell_exec('cat /sys/class/dmi/id/product_uuid 2>/dev/null');
        $productUuid = $output ? trim($output) : null;
        if (!empty($productUuid) && $productUuid !== '00000000-0000-0000-0000-000000000000') {
            $hardwareInfo[] = 'uuid:' . str_replace('-', '', strtoupper($productUuid));
        }
    }

    // 方法7: 获取BIOS信息（DMI）- 在虚拟化环境中可能不可用
    $output = @shell_exec('cat /sys/class/dmi/id/bios_version 2>/dev/null');
    $biosVersion = $output ? trim($output) : null;
    if (!empty($biosVersion)) {
        $hardwareInfo[] = 'bios:' . md5($biosVersion);
    }

    // 方法8: 使用系统信息组合（作为额外补充，提高硬件ID的唯一性和可靠性）
    // 即使已有足够的硬件信息，也添加系统信息作为补充
    $systemInfo = [];

    // 获取 hostname
    $hostname = @gethostname();
    if ($hostname) {
        $systemInfo[] = 'hostname:' . $hostname;
    }

    // 获取 boot_id（Linux内核启动ID，每次启动都不同，但可以作为系统标识）
    $bootId = @file_get_contents('/proc/sys/kernel/random/boot_id');
    if ($bootId) {
        $systemInfo[] = 'boot:' . trim($bootId);
    }

    // 获取系统版本信息
    $osRelease = @file_get_contents('/etc/os-release');
    if ($osRelease) {
        $osId = extract_wmic_value($osRelease, '/^ID=["\']?([^"\'\n]+)["\']?/im');
        $osVersionId = extract_wmic_value($osRelease, '/^VERSION_ID=["\']?([^"\'\n]+)["\']?/im');
        if ($osId) {
            $systemInfo[] = 'os:' . $osId . ($osVersionId ? ':' . $osVersionId : '');
        }
    }

    // 获取内核版本
    $kernelVersion = @php_uname('r');
    if ($kernelVersion) {
        $systemInfo[] = 'kernel:' . $kernelVersion;
    }

    // 如果系统信息不为空，添加为硬件标识
    if (!empty($systemInfo)) {
        $hardwareInfo[] = 'system:' . md5(implode('|', $systemInfo));
    }

    return $hardwareInfo;
}

/**
 * 获取macOS系统的硬件信息
 * @return array<string> 硬件信息数组
 */
function get_macos_hardware_info(): array
{
    $hardwareInfo = [];

    // 方法1: 获取物理网卡MAC地址
    $output = @shell_exec("networksetup -listallhardwareports 2>/dev/null | grep -A 1 'Hardware Port' | grep -v 'Hardware Port' | head -1");
    if ($output) {
        $interface = trim($output);
        $output2 = @shell_exec("ifconfig {$interface} 2>/dev/null | grep -oE '([0-9a-f]{2}:){5}[0-9a-f]{2}' | head -1");
        $macAddress = extract_mac_address($output2, '/([0-9a-f]{2}:){5}[0-9a-f]{2}/i');
        if ($macAddress) {
            $hardwareInfo[] = 'mac:' . $macAddress;
        }
    }

    // 备用方法：使用 ifconfig
    if (empty($hardwareInfo)) {
        $output = @shell_exec("ifconfig 2>/dev/null | grep -E '^[a-z0-9]+' | grep -v 'lo0:' | head -1");
        if ($output) {
            $interface = preg_split('/\s+/', trim($output))[0] ?? '';
            if ($interface) {
                $output2 = @shell_exec("ifconfig {$interface} 2>/dev/null | grep -oE '([0-9a-f]{2}:){5}[0-9a-f]{2}' | head -1");
                $macAddress = extract_mac_address($output2, '/([0-9a-f]{2}:){5}[0-9a-f]{2}/i');
                if ($macAddress) {
                    $hardwareInfo[] = 'mac:' . $macAddress;
                }
            }
        }
    }

    // 方法2: 获取硬件UUID（最可靠）
    $output = @shell_exec('system_profiler SPHardwareDataType 2>/dev/null | grep "Hardware UUID"');
    $hardwareUuid = extract_wmic_value($output, '/Hardware UUID:\s*([^\r\n]+)/i');
    if ($hardwareUuid) {
        $hardwareInfo[] = 'uuid:' . str_replace('-', '', strtoupper($hardwareUuid));
    }

    // 方法3: 获取序列号
    $output = @shell_exec('system_profiler SPHardwareDataType 2>/dev/null | grep "Serial Number"');
    $systemSerialNumber = extract_wmic_value($output, '/Serial Number \(system\):\s*([^\r\n]+)/i');
    if ($systemSerialNumber) {
        $hardwareInfo[] = 'serial:' . $systemSerialNumber;
    }

    // 方法4: 获取平台UUID
    $output = @shell_exec('ioreg -rd1 -c IOPlatformExpertDevice 2>/dev/null | grep IOPlatformUUID');
    $platformUuid = extract_wmic_value($output, '/"IOPlatformUUID"=\s*"([^"]+)"/i');
    if ($platformUuid) {
        $hardwareInfo[] = 'platform:' . str_replace('-', '', strtoupper($platformUuid));
    }

    return $hardwareInfo;
}

/**
 * 获取硬件ID（增强版本，获取真实的机器码）
 * 组合多个硬件特征以确保唯一性和安全性
 * @param string|null $hardwareIdSpecified 用户指定的硬件ID（可选）
 * @return string 16位十六进制硬件ID
 */
function get_hardware_id(?string $hardwareIdSpecified = null): string
{
    // 如果用户指定了硬件ID，验证并返回
    $normalizedId = validate_and_normalize_hardware_id($hardwareIdSpecified);
    if ($normalizedId !== null) {
        return $normalizedId;
    }

    // 根据操作系统获取硬件信息
    $hardwareInfo = match (PHP_OS_FAMILY) {
        'Windows' => get_windows_hardware_info(),
        'Linux' => get_linux_hardware_info(),
        'Darwin' => get_macos_hardware_info(),
        default => []
    };

    // 如果以上方法都失败，使用系统信息组合作为备用
    if (empty($hardwareInfo)) {
        $hostname = @gethostname() ?: '';
        $uname = @php_uname() ?: '';
        $hardwareInfo[] = 'fallback:' . md5($hostname . $uname . PHP_OS);
    }

    // 组合所有硬件信息并生成哈希
    $combinedHardwareInfo = implode('|', $hardwareInfo);
    $hardwareHash = hash('sha256', $combinedHardwareInfo);

    // 取前16个字符作为硬件ID并规范化
    $hardwareId = strtoupper(substr($hardwareHash, 0, 16));
    return str_pad($hardwareId, 16, '0', STR_PAD_RIGHT);
}

/**
 * 计算文件的哈希值（使用硬件ID作为盐值）
 * @param string $filePath 文件路径
 * @param string $hardwareId 硬件ID
 * @return string|null 文件的哈希值，失败返回null
 */
function calculate_file_hash(string $filePath, string $hardwareId): ?string
{
    if (!file_exists($filePath)) {
        return null;
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        return null;
    }

    // 使用硬件ID作为盐值，增加安全性
    return hash_hmac('sha256', $content, $hardwareId);
}