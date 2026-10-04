<?php

class RouterCompoundNameController extends Controller
{
    public array $receivedParameters = [];

    public function show(string $id): void
    {
        $this->receivedParameters = [$id];
    }
}
