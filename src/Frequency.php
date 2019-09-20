<?php

namespace CloverSwoole\Database;


class Frequency
{
    /**
     * @var array
     */
    protected $hits = [];

    /**
     * How much time do you want to calculate the frequency ?
     * @var int
     */
    protected $time = 10;

    /**
     * @var int
     */
    protected $lowFrequency = 5;

    /**
     * @var int
     */
    protected $beginTime;

    public function __construct()
    {
        $this->beginTime = time();
    }

    public function hit(int $number = 1): bool
    {
        $this->flush();

        $now = time();
        $hit = $this->hits[$now] ?? 0;
        $this->hits[$now] = $number + $hit;

        return true;
    }

    public function frequency(): float
    {
        $this->flush();

        $hits = 0;
        $count = 0;
        foreach ($this->hits as $hit) {
            ++$count;
            $hits += $hit;
        }

        return floatval($hits / $count);
    }

    public function isLowFrequency(): bool
    {
        return $this->frequency() < $this->lowFrequency;
    }

    protected function flush(): void
    {
        $now = time();
        $latest = $now - $this->time;
        foreach ($this->hits as $time => $hit) {
            if ($time < $latest) {
                unset($this->hits[$time]);
            }
        }

        if (count($this->hits) < $this->time) {
            $beginTime = $this->beginTime < $latest ? $latest : $this->beginTime;
            for ($i = $beginTime; $i < $now; ++$i) {
                $this->hits[$i] = $this->hits[$i] ?? 0;
            }
        }
    }
}
