<?php

namespace Core;

class Sys
{

    /**
     * Отображение 404-ошибки
     */
    public static function show_404()
    {
        echo self::render('./app/views/_layouts/404.php');
        exit;
    }


    /**
     * Преобразование дат из ISO в нужный формат
     */
    public static function date($date, $format)
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        if (!$dt) {
            $dt = \DateTime::createFromFormat('Y-m-d', $date);
        }
        return $dt ? $dt->format($format) : null;
    }


    /**
     * Преобразование сумм
     */
    public static function number($n, $decimal = true)
    {
        return number_format($n, $decimal ? 2 : 0, ',', ' ');
    }


    /**
     * Отправка сообщения на e-mail
     *
     * @param $to - получатели
     * @param $subject - заголовок
     * @param $body - текст письма
     * @param array $attachments - вложения
     * @return bool
     */
    public static function mail($to, $subject, $body, $attachments = array())
    {
        $config = $GLOBALS['config']['send_mail'];

        $mail = new \Libs\PHPMailer;
        $mail->CharSet = 'utf-8';
        $mail->From = $config['from_email'];
        $mail->FromName = $config['from_name'];
        if ($config['smtp']) {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->Port = $config['port'];
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = $config['smtp_secure'];
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
        }
        foreach (explode(',', $to) as $_) {
            $mail->addAddress(trim($_));
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        return $mail->send();
    }


    /**
     * Рендер шаблонов
     *
     * @param $viewFile - файл шаблона
     * @param $viewData - данные шаблона
     * @return string
     */
    public static function render($viewFile, $viewData = array())
    {
        extract($viewData, EXTR_PREFIX_SAME, 'data');
        ob_start();
        ob_implicit_flush(false);
        require($viewFile);
        return ob_get_clean();
    }

}