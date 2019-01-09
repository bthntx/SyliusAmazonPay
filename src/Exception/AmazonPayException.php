<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 01.11.2018
 * Time: 21:32
 */

namespace Bthn\SyliusAmazonPayPlugin\Exception;

use Payum\Core\Exception\Http\HttpException;


class AmazonPayException extends HttpException
{
    const LABEL = 'AmazonPayException';
    public static function newInstance($status)
    {
        $parts = [self::LABEL];
        if (property_exists($status, 'statusLiteral')) {
            $parts[] = '[reason literal] ' . $status->statusLiteral;
        }
        if (property_exists($status, 'statusCode')) {
            $parts[] = '[status code] ' . $status->statusCode;
        }
        if (property_exists($status, 'statusDesc')) {
            $parts[] = '[reason phrase] ' . $status->statusDesc;
        }
        $message = implode(PHP_EOL, $parts);
        return new static($message);
    }
}