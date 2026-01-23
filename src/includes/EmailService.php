<?php
/**
 * EmailService - Manejo de envío de emails
 * Versión simplificada sin dependencias externas
 */

class EmailService {
    private $gmailEmail;
    private $gmailPassword;
    
    public function __construct() {
        $this->gmailEmail = $_ENV['GMAIL_EMAIL'] ?? '';
        $this->gmailPassword = $_ENV['GMAIL_PASSWORD'] ?? '';
    }
    
    /**
     * Enviar credenciales a un docente nuevo
     */
    public function enviarCredencialesDocente($email, $nombre, $apellido, $tempPassword) {
        try {
            // Validar que Gmail esté configurado
            if (empty($this->gmailEmail) || empty($this->gmailPassword)) {
                logWarning('EMAIL_NOT_CONFIGURED', 'Gmail no está configurado');
                return ['success' => false, 'message' => 'Email no configurado en el servidor'];
            }
            
            $asunto = 'ClassControl - Tus Credenciales de Acceso';
            $htmlBody = $this->generarHTMLCredenciales($nombre, $apellido, $email, $tempPassword);
            
            // Headers para envío de HTML con configuración de Gmail
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $this->gmailEmail . "\r\n";
            $headers .= "Reply-To: " . $this->gmailEmail . "\r\n";
            $headers .= "X-Mailer: ClassControl/1.0\r\n";
            
            // Intenta enviar el email
            $enviado = @mail($email, $asunto, $htmlBody, $headers);
            
            if ($enviado) {
                logInfo('EMAIL_SENT_SUCCESS', [
                    'email' => $email,
                    'nombre' => $nombre,
                    'from' => $this->gmailEmail
                ]);
                return ['success' => true, 'message' => 'Email enviado correctamente a ' . $email];
            } else {
                logError('EMAIL_SEND_FAILED', "No se pudo enviar email a $email. Verifica la configuración de SMTP del servidor.");
                return ['success' => false, 'message' => 'El email no pudo enviarse. Las credenciales se guardaron pero no fueron enviadas.'];
            }
            
        } catch (\Exception $e) {
            logError('EMAIL_EXCEPTION', $e->getMessage());
            return ['success' => false, 'message' => 'Error al enviar email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Generar HTML bonito para las credenciales
     */
    private function generarHTMLCredenciales($nombre, $apellido, $email, $password) {
        $appUrl = getAppUrl();
        $ano = date('Y');
        
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 28px; }
        .header p { margin: 8px 0 0 0; font-size: 14px; opacity: 0.9; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .greeting { font-size: 16px; margin-bottom: 20px; }
        .credentials { background: white; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .credential-item { margin: 15px 0; }
        .credential-label { font-weight: bold; color: #667eea; font-size: 14px; text-transform: uppercase; }
        .credential-value { background: #f0f0f0; padding: 10px; margin-top: 5px; font-family: monospace; border-radius: 4px; word-break: break-all; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; font-size: 14px; }
        .button-container { text-align: center; margin: 25px 0; }
        .button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .button:hover { opacity: 0.9; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; }
        .footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 ClassControl</h1>
            <p>Sistema de Gestión de Horarios Académicos</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                <p>¡Hola <strong>$nombre $apellido</strong>!</p>
                <p>Tu cuenta en <strong>ClassControl</strong> ha sido creada exitosamente. A continuación encontrarás tus credenciales de acceso inicial.</p>
            </div>
            
            <div class="credentials">
                <div class="credential-item">
                    <div class="credential-label">📧 Email</div>
                    <div class="credential-value">$email</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">🔑 Contraseña Temporal</div>
                    <div class="credential-value">$password</div>
                </div>
            </div>
            
            <div class="warning">
                <strong>⚠️ Importante:</strong> Esta es una contraseña temporal. Te recomendamos cambiarla por una más segura después de tu primer acceso.
            </div>
            
            <div class="button-container">
                <a href="$appUrl/public/index.html" class="button">👉 Acceder a ClassControl</a>
            </div>
            
            <div style="margin: 25px 0; padding: 15px; background: #e7f3ff; border-radius: 4px; font-size: 14px;">
                <strong>📝 Próximos pasos:</strong>
                <ol>
                    <li>Inicia sesión con las credenciales proporcionadas</li>
                    <li>Dirígete a tu perfil y cambia tu contraseña</li>
                    <li>Completa tu información de disponibilidad horaria</li>
                    <li>¡Comienza a utilizar el sistema!</li>
                </ol>
            </div>
            
            <p style="margin: 20px 0; font-size: 14px; color: #666;">Si tienes problemas para acceder o necesitas ayuda, contacta con el administrador del sistema.</p>
        </div>
        
        <div class="footer">
            <p><strong>ClassControl v1.0</strong> - Sistema Profesional de Gestión de Horarios</p>
            <p>© $ano - Todos los derechos reservados</p>
            <p style="color: #bbb; margin-top: 10px;">Este es un mensaje automático, por favor no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Enviar notificación genérica
     */
    public function enviarNotificacion($destinatario, $asunto, $contenido, $htmlContent = null) {
        try {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "From: " . $this->gmailEmail . "\r\n";
            $headers .= "Reply-To: " . $this->gmailEmail . "\r\n";
            
            if ($htmlContent) {
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $body = $htmlContent;
            } else {
                $headers .= "Content-type: text/plain; charset=UTF-8\r\n";
                $body = $contenido;
            }
            
            $enviado = mail($destinatario, $asunto, $body, $headers);
            return ['success' => $enviado];
        } catch (\Exception $e) {
            logError('EMAIL_ERROR', $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>

