<?php

namespace App\Traits;

use App\Services\ChatGptService;

trait AIArticleable
{
    protected $service;

    public function generateArticle($data, $type)
    {
        $this->loadService();
        return $this->service->generateArticle($data, $type);
    }

    public function loadService()
    {
        $this->service = new ChatGptService();
    }

}
