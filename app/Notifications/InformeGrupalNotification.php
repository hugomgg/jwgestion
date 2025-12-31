<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InformeGrupalNotification extends Notification
{
    use Queueable;

    public $grupoNombre;
    public $publicadorNombre;
    public $congregacionNombre;
    public $periodo;
    public $informesData;

    /**
     * Create a new notification instance.
     */
    public function __construct($grupoNombre, $publicadorNombre, $congregacionNombre, $periodo, $informesData)
    {
        $this->grupoNombre = $grupoNombre;
        $this->publicadorNombre = $publicadorNombre;
        $this->congregacionNombre = $congregacionNombre;
        $this->periodo = $periodo;
        $this->informesData = $informesData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $subject = "{$this->grupoNombre} - {$this->publicadorNombre} - {$this->periodo}";

        // Construir la tabla HTML
        $tablaHtml = $this->construirTablaHtml();

        return (new MailMessage)
            ->subject($subject)
            ->greeting('¡Hola, ' . $notifiable->name . '!')
            ->line("Se ha recibido un nuevo informe de **{$this->publicadorNombre}** del grupo **{$this->grupoNombre}** (Congregación: **{$this->congregacionNombre}**) para el periodo **{$this->periodo}**.")
            ->line('A continuación se muestra el resumen de todos los informes recibidos para este grupo en el periodo seleccionado:')
            ->line('')
            ->line(new \Illuminate\Support\HtmlString($tablaHtml))
            ->line('')
            ->salutation('Saludos,')
            ->salutation('El equipo de ' . $appName);
    }

    /**
     * Construir la tabla HTML con los informes del grupo
     */
    private function construirTablaHtml()
    {
        $html = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; font-family: Arial, sans-serif;">';
        
        // Encabezado de la tabla
        $html .= '<thead>';
        $html .= '<tr style="background-color: #343a40; color: white;">';
        $html .= '<th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Nombre</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 12px; text-align: center; width: 10%;">Participa</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 12px; text-align: left; width: 15%;">Servicio</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 12px; text-align: center; width: 10%;">Estudios</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 12px; text-align: center; width: 10%;">Horas</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 12px; text-align: left; width: 20%;">Comentario</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        
        // Cuerpo de la tabla
        $html .= '<tbody>';
        
        foreach ($this->informesData as $index => $informe) {
            $bgColor = $index % 2 === 0 ? '#f8f9fa' : '#ffffff';
            
            $html .= '<tr style="background-color: ' . $bgColor . ';">';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($informe['nombre']) . '</td>';
            
            // Icono de participa
            if ($informe['participa'] == 1) {
                $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><span style="color: #28a745; font-weight: bold; font-size: 16px;">✓</span></td>';
            } else {
                $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><span style="color: #dc3545; font-weight: bold; font-size: 16px;">✗</span></td>';
            }
            
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($informe['servicio_nombre']) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . htmlspecialchars($informe['cantidad_estudios']) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . htmlspecialchars($informe['horas']) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($informe['comentario']) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        
        return $html;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
