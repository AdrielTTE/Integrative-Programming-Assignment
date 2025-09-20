<?php

namespace App\Observers;

class AnnouncementSubject implements Subject
{
    protected $observers = [];
    protected $message;

    public function __construct(string $message)
    {
        $this->message = $message;
         $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

    public function addObserver(Observer $observer): void
    {
        $this->observers[] = $observer;
    }

    public function removeObserver(Observer $observer): void
    {
        $this->observers = array_filter($this->observers, function ($obs) use ($observer) {
            return $obs !== $observer;
        });
    }

    public function notifyObserver(): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
