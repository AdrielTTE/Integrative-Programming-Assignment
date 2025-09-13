<?php

namespace App\Observers;

use App\Observers\Observer;

interface Subject {
    function addObserver(Observer $observer);
    function removeObserver(Observer $observer);
    function notifyObserver();
}
