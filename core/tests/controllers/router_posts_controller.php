<?php

class RouterPostsController extends Controller
{
    public int $showCalls = 0;
    public array $receivedParameters = [];

    public function show(string $id): void
    {
        ++$this->showCalls;
        $this->receivedParameters = [$id];
    }
}
