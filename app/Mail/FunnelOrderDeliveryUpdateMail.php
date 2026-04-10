<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FunnelOrderDeliveryUpdateMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $funnelName;
    public string $customerName;
    public string $deliveryStatus;
    public ?string $trackingUrl;
    public string $courierName;
    public array $orderItems;
    public int $orderQuantity;
    public ?string $customMessage;

    public function __construct(
        string $funnelName,
        string $customerName,
        string $deliveryStatus,
        ?string $trackingUrl = null,
        string $courierName = 'LBC',
        array $orderItems = [],
        int $orderQuantity = 0,
        ?string $customMessage = null
    ) {
        $this->funnelName = $funnelName;
        $this->customerName = $customerName;
        $this->deliveryStatus = $deliveryStatus;
        $this->trackingUrl = $trackingUrl;
        $this->courierName = $courierName;
        $this->orderItems = $orderItems;
        $this->orderQuantity = $orderQuantity;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        $statusLabel = ucwords(str_replace('_', ' ', $this->deliveryStatus));

        return $this
            ->subject('Order Update: ' . $statusLabel . ' - ' . $this->funnelName)
            ->view('emails.funnels.order-delivery-update');
    }
}
