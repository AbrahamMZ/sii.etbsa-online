<?php

namespace App\Notifications;

use App\Components\Purchase\Models\PurchaseOrder;
use App\Components\User\Models\Group;
use App\Components\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderUpdatedNotification extends Notification
{
    use Queueable;

    private $purchaseOrder;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $ccUsers = User::All()
            ->map(function ($user) {
                if ($user->hasPermission('compras.admin')) {
                    return $user->email;
                }
            })->filter()->toArray();

        $uri = url('/admin#/purchases/list');
        return (new MailMessage)
            ->subject('Actualizacion en Orden de Compra - #' . $this->purchaseOrder->id)
            ->cc($ccUsers)
            ->greeting('Buen dia, Se Actualizo el Estatus en su Orden de Compra')
            ->line('Folio Orden de Compra: #' . $this->purchaseOrder->id)
            ->line('ESTATUS Actual: ' . $this->purchaseOrder->estatus->title)
            ->line('Usuario quien Actualizo: ' . $this->purchaseOrder->updated_user->name)
            ->action('Ir a Ordenes de Compra', $uri)
            ->line('Gracias por usar nuestra Aplicacion.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
