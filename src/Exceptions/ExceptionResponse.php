<?php

namespace Aether\Exceptions;

use Exception;
use Aether\Response\Response;

class ExceptionResponse extends Response
{
    protected $exception;

    protected $reportedId;

    protected $content;

    public function __construct(Exception $exception, $reportedId = null)
    {
        $this->exception = $exception;

        $this->reportedId = $reportedId;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function draw($aether)
    {
        http_response_code(500);

        header('Content-Type: text/html; charset=utf-8');

        if ($this->reportedId) {
            header("X-Error-ID: {$this->reportedId}");
        }

        echo $this->get();
    }

    public function get()
    {
        return $this->content;
    }
}
