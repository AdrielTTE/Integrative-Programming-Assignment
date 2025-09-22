<?php


namespace App\Observers;

use App\Models\Package;

class PackageSubject implements Subject
{
    protected $observers = [];
    protected $package;

    public function __construct(Package $package)
    {
        $this->package = $package;
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

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function setPackage(Package $package)
{
    $this->package = $package;
}

}
