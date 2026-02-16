<?php
/**
 * EmailService - Manejo de envío de emails
 * Soporta múltiples métodos: mail() de PHP o SMTP directo
 */

class EmailService {
    private $gmailEmail;
    private $gmailPassword;
    private $mailHost;
    private $mailPort;
    
    public function __construct() {
        // Usar configuración definida en config.php o variables de entorno
        $this->gmailEmail = defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : ($_ENV['GMAIL_EMAIL'] ?? '');
        $this->gmailPassword = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : ($_ENV['GMAIL_PASSWORD'] ?? '');
        $this->mailHost = defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com';
        $this->mailPort = defined('MAIL_PORT') ? MAIL_PORT : 587;
    }
    
    /**
     * Enviar credenciales a un administrador nuevo
     */
    public function enviarCredencialesAdministrador($email, $nombre, $apellido, $tempPassword) {
        try {
            // Si no hay credenciales, simplemente registrar que se guardaron pero no se enviaron
            if (empty($this->gmailEmail)) {
                logWarning('EMAIL_NOT_CONFIGURED', 'Email no está configurado');
                return [
                    'success' => true,
                    'message' => 'Credenciales creadas. (Email: No configurado en servidor)',
                    'email_sent' => false
                ];
            }
            
            $asunto = 'ClassControl - Credenciales de Administrador';
            $htmlBody = $this->generarHTMLCredencialesAdmin($nombre, $apellido, $email, $tempPassword);
            
            return $this->enviarEmail($email, $asunto, $htmlBody);
        } catch (\Exception $e) {
            logError('EMAIL_EXCEPTION', $e->getMessage());
            return [
                'success' => true,
                'message' => 'Credenciales creadas. (Error al enviar email)',
                'email_sent' => false
            ];
        }
    }
    
    /**
     * Enviar credenciales a un docente nuevo
     */
    public function enviarCredencialesDocente($email, $nombre, $apellido, $tempPassword) {
        try {
            // Si no hay credenciales configuradas, aún así consideramos éxito las credenciales guardadas
            if (empty($this->gmailEmail)) {
                logWarning('EMAIL_NOT_CONFIGURED', 'Email no está configurado');
                return [
                    'success' => true,
                    'message' => 'Credenciales creadas. (Email: No configurado en servidor)',
                    'email_sent' => false
                ];
            }
            
            $asunto = 'ClassControl - Tus Credenciales de Acceso';
            $htmlBody = $this->generarHTMLCredenciales($nombre, $apellido, $email, $tempPassword);
            
            return $this->enviarEmail($email, $asunto, $htmlBody);
            
        } catch (\Exception $e) {
            logError('EMAIL_EXCEPTION', $e->getMessage());
            return [
                'success' => true,
                'message' => 'Credenciales creadas. (Error al enviar email)',
                'email_sent' => false
            ];
        }
    }
    
    /**
     * Método principal para enviar emails
     */
    private function enviarEmail($destinatario, $asunto, $htmlBody) {
        $headers = $this->construirHeaders();
        
        // Intentar enviar con mail() de PHP
        $enviado = @mail($destinatario, $asunto, $htmlBody, $headers);
        
        if ($enviado) {
            logInfo('EMAIL_SENT_SUCCESS', [
                'email' => $destinatario,
                'from' => $this->gmailEmail,
                'method' => 'php_mail'
            ]);
            return [
                'success' => true,
                'message' => 'Email enviado correctamente a ' . $destinatario,
                'email_sent' => true
            ];
        } else {
            // Si falla, registrar pero no fallar completamente
            // El usuario ya fue creado, solo no se envió el email
            logWarning('EMAIL_SEND_FAILED', "No se pudo enviar email a $destinatario. Verifica la configuración SMTP del servidor.");
            return [
                'success' => true,
                'message' => 'Credenciales creadas. (Email: No se pudo enviar)',
                'email_sent' => false
            ];
        }
    }
    
    /**
     * Construir headers del email
     */
    private function construirHeaders() {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        if (!empty($this->gmailEmail)) {
            $headers .= "From: " . $this->gmailEmail . "\r\n";
            $headers .= "Reply-To: " . $this->gmailEmail . "\r\n";
        }
        $headers .= "X-Mailer: ClassControl/1.0\r\n";
        return $headers;
    }
    
    /**
     * Generar HTML bonito para las credenciales del administrador
     */
    private function generarHTMLCredencialesAdmin($nombre, $apellido, $email, $password) {
        $appUrl = function_exists('getAppUrl') ? getAppUrl() : '';
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
        .credentials { background: white; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .credential-item { margin: 15px 0; }
        .credential-label { font-weight: bold; color: #667eea; font-size: 14px; text-transform: uppercase; }
        .credential-value { background: #f0f0f0; padding: 10px; margin-top: 5px; font-family: monospace; border-radius: 4px; word-break: break-all; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; font-size: 14px; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; }
        .footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 ClassControl - Admin</h1>
            <p>Sistema de Gestión de Horarios Académicos</p>
        </div>
        
        <div class="content">
            <p>¡Hola <strong>$nombre $apellido</strong>!</p>
            <p>Se ha creado una nueva cuenta de <strong>Administrador</strong> en ClassControl.</p>
            
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
                <strong>⚠️ Importante:</strong> Esta es una contraseña temporal. Cámbiala en tu primer acceso.
            </div>
            
            <p style="font-size: 14px; color: #666;">Para acceder al sistema, ingresa a la plataforma con estas credenciales.</p>
        </div>
        
        <div class="footer">
            <p><strong>ClassControl v1.0</strong> - Sistema Profesional de Gestión de Horarios</p>
            <p>© $ano - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Generar HTML bonito para las credenciales
     */
    private function generarHTMLCredenciales($nombre, $apellido, $email, $password) {
        $appUrl = function_exists('getAppUrl') ? getAppUrl() : '';
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
            $headers = $this->construirHeaders();
            
            if ($htmlContent) {
                $body = $htmlContent;
            } else {
                $body = $contenido;
            }
            
            $enviado = @mail($destinatario, $asunto, $body, $headers);
            return ['success' => $enviado, 'message' => $enviado ? 'Notificación enviada' : 'No se pudo enviar la notificación'];
        } catch (\Exception $e) {
            logError('EMAIL_ERROR', $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>


