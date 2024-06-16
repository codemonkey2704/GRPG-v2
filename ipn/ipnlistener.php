<?php
declare(strict_types=1);

/**
 *  PayPal IPN Listener.
 *
 *  A class to listen for and handle Instant Payment Notifications (IPN) from
 *  the PayPal server.
 *
 *  https://github.com/Quixotix/PHP-PayPal-IPN
 *
 *  @author     Micah Carrick
 *  @copyright  (c) 2012 - Micah Carrick
 *
 *  @version    2.1.0
 */
class ipnlistener
{
    /**
     *  If true, the recommended cURL PHP library is used to send the post back
     *  to PayPal. If false then fsockopen() is used. Default true.
     *
     *  @var bool
     */
    public bool $use_curl = true;
    /**
     *  If true, explicitly sets cURL to use SSL version 3. Use this if cURL
     *  is compiled with GnuTLS SSL.
     *
     *  @var bool
     */
    public bool $force_ssl_v3 = false;
    /**
     *  If true, cURL will use the CURLOPT_FOLLOWLOCATION to follow any
     *  "Location: ..." headers in the response.
     *
     *  @var bool
     */
    public bool $follow_location = false;
    /**
     *  If true, an SSL secure connection (port 443) is used for the post back
     *  as recommended by PayPal. If false, a standard HTTP (port 80) connection
     *  is used. Default true.
     *
     *  @var bool
     */
    public bool $use_ssl = false;
    /**
     *  If true, the paypal sandbox URI www.sandbox.paypal.com is used for the
     *  post back. If false, the live URI www.paypal.com is used. Default false.
     *
     *  @var bool
     */
    public bool $use_sandbox = false;
    /**
     *  The amount of time, in seconds, to wait for the PayPal server to respond
     *  before timing out. Default 30 seconds.
     *
     *  @var int
     */
    public int $timeout = 30;
    private array $post_data = [];
    private string $post_uri = '';
    private string $response_status = '';
    private string $response = '';
    public const PAYPAL_HOST = 'www.paypal.com';
    public const SANDBOX_HOST = 'www.sandbox.paypal.com';

    /**
     *  Post Back Using cURL.
     *
     *  Sends the post back to PayPal using the cURL library. Called by
     *  the processIpn() method if the use_curl property is true. Throws an
     *  exception if the post fails. Populates the response, response_status,
     *  and post_uri properties on success.
     *
     * @param string  The post data as a URL encoded string
     *
     * @throws Exception
     * @throws Exception
     */
    protected function curlPost($encoded_data): void
    {
        if ($this->use_ssl) {
            $uri = 'https://'.$this->getPaypalHost().'/cgi-bin/webscr';
            $this->post_uri = $uri;
        } else {
            $uri = 'https://'.$this->getPaypalHost().'/cgi-bin/webscr';
            $this->post_uri = $uri;
        }
        $curlOpts = [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => __DIR__.'/cert/cacert.pem',
            CURLOPT_URL => $uri,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $encoded_data,
            CURLOPT_FOLLOWLOCATION => $this->follow_location,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
        ];
        if ($this->force_ssl_v3) {
            $curlOpts[CURLOPT_SSLVERSION] = 3;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $curlOpts);
        $this->response = curl_exec($ch);
        $this->response_status = (string)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($this->response === false || $this->response_status === '0') {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            throw new \RuntimeException("cURL error: [$errno] $errstr");
        }
    }

    /**
     *  Post Back Using fsockopen().
     *
     *  Sends the post back to PayPal using the fsockopen() function. Called by
     *  the processIpn() method if the use_curl property is false. Throws an
     *  exception if the post fails. Populates the response, response_status,
     *  and post_uri properties on success.
     *
     * @param string  The post data as a URL encoded string
     *
     * @throws Exception
     * @throws Exception
     */
    protected function fsockPost($encoded_data): void
    {
        if ($this->use_ssl) {
            $uri = 'ssl://'.$this->getPaypalHost();
            $port = '443';
            $this->post_uri = $uri.'/cgi-bin/webscr';
        } else {
            $uri = $this->getPaypalHost(); // no "https://" in call to fsockopen()
            $port = '80';
            $this->post_uri = 'https://'.$uri.'/cgi-bin/webscr';
        }
        $fp = fsockopen($uri, $port, $errno, $errstr, $this->timeout);
        if (!$fp) {
            // fsockopen error
            throw new \RuntimeException("fsockopen error: [$errno] $errstr");
        }
        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= 'Host: '.$this->getPaypalHost()."\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= 'Content-Length: '.strlen($encoded_data)."\r\n";
        $header .= "Connection: Close\r\n\r\n";
        fwrite($fp, $header.$encoded_data."\r\n\r\n");
        while (!feof($fp)) {
            if (empty($this->response)) {
                // extract HTTP status from first line
                $this->response .= $status = fgets($fp, 1024);
                $this->response_status = trim(substr($status, 9, 4));
            } else {
                $this->response .= fgets($fp, 1024);
            }
        }
        fclose($fp);
    }

    private function getPaypalHost(): string
    {
        if ($this->use_sandbox) {
            return self::SANDBOX_HOST;
        }

        return self::PAYPAL_HOST;
    }

    /**
     *  Get POST URI.
     *
     *  Returns the URI that was used to send the post back to PayPal. This can
     *  be useful for troubleshooting connection problems. The default URI
     *  would be "ssl://www.sandbox.paypal.com:443/cgi-bin/webscr"
     *
     *  @return string
     */
    public function getPostUri(): string
    {
        return $this->post_uri;
    }

    /**
     *  Get Response.
     *
     *  Returns the entire response from PayPal as a string including all the
     *  HTTP headers.
     *
     *  @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     *  Get Response Status.
     *
     *  Returns the HTTP response status code from PayPal. This should be "200"
     *  if the post back was successful.
     *
     *  @return string
     */
    public function getResponseStatus(): string
    {
        return $this->response_status;
    }

    /**
     *  Get Text Report.
     *
     *  Returns a report of the IPN transaction in plain text format. This is
     *  useful in emails to order processors and system administrators. Override
     *  this method in your own class to customize the report.
     *
     *  @return string
     */
    public function getTextReport(): string
    {
        $r = '';
        // date and POST url
        for ($i = 0; $i < 80; ++$i) {
            $r .= '-';
        }
        $r .= "\n[".date('m/d/Y g:i A').'] - '.$this->getPostUri();
        if ($this->use_curl) {
            $r .= " (curl)\n";
        } else {
            $r .= " (fsockopen)\n";
        }
        // HTTP Response
        for ($i = 0; $i < 80; ++$i) {
            $r .= '-';
        }
        $r .= "\n{$this->getResponse() }\n";
        // POST vars
        for ($i = 0; $i < 80; ++$i) {
            $r .= '-';
        }
        $r .= "\n";
        foreach ($this->post_data as $key => $value) {
            $r .= str_pad($key, 25)."$value\n";
        }
        $r .= "\n\n";

        return $r;
    }

    /**
     *  Process IPN.
     *
     *  Handles the IPN post back to PayPal and parsing the response. Call this
     *  method from your IPN listener script. Returns true if the response came
     *  back as "VERIFIED", false if the response came back "INVALID", and
     *  throws an exception if there is an error.
     *
     * @param array
     *
     * @return bool
     * @throws Exception
     * @throws Exception
     */
    public function processIpn($post_data = null): ?bool
    {
        $encoded_data = 'cmd=_notify-validate';
        if ($post_data === null) {
            // use raw POST data
            if (!empty($_POST)) {
                $this->post_data = $_POST;
                $encoded_data .= '&'.file_get_contents('php://input');
            } else {
                throw new \RuntimeException('No POST data found.');
            }
        } else {
            // use provided data array
            $this->post_data = $post_data;
            foreach ($this->post_data as $key => $value) {
                $encoded_data .= "&$key=".urlencode($value);
            }
        }
        if ($this->use_curl) {
            $this->curlPost($encoded_data);
        } else {
            $this->fsockPost($encoded_data);
        }
        if (strpos($this->response_status, '200') === false) {
            throw new \RuntimeException('Invalid response status: '.$this->response_status);
        }
        if (strpos($this->response, 'VERIFIED') !== false) {
            return true;
        }

        if (strpos($this->response, 'INVALID') !== false) {
            return false;
        }

        throw new \RuntimeException('Unexpected response from PayPal.');
    }

    /**
     *  Require Post Method.
     *
     *  Throws an exception and sets a HTTP 405 response header if the request
     *  method was not POST.
     *
     * @throws Exception
     */
    public function requirePostMethod(): void
    {
        // require POST requests
        if ($_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST', true, 405);
            throw new \RuntimeException('Invalid HTTP request method.');
        }
    }
}
