<?php

declare (strict_types=1);

namespace app\kernel\plugin\entity;

class Payment
{
    private int $renderMode;
    private ?string $payUrl = null;
    private array $options = [];

    public function getRenderMode(): int
    {
        return $this->renderMode;
    }

    public function setRenderMode(int $renderMode): void
    {
        $this->renderMode = $renderMode;
    }

    public function getPayUrl(): string
    {
        return $this->payUrl;
    }

    public function setPayUrl(string $payUrl): void
    {
        $this->payUrl = $payUrl;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOption(array $options): void
    {
        $this->options = $options;
    }
}