<?php


namespace App\Observers;

use App\Models\Delivery;

class DeliverySubject implements Subject
{
    protected $observers = [];
    protected $delivery;

    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    public function addObserver(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    public function removeObserver(Observer $observer)
    {
        $this->observers = array_filter($this->observers, function ($obs) use ($observer) {
            return $obs !== $observer;
        });
    }

    public function notifyObserver()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function getPackage(): Delivery
    {
        return $this->delivery;
    }
}
