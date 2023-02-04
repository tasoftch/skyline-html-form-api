<?php

namespace Skyline\HTML\Form\Action;


use Skyline\API\Exception\APIException;
use Skyline\HTML\Form\FormElement;

class APIAction implements ActionInterface
{
    /** @var string */
    private $apiURI;

    private $successFunctionName;
    private $errorFunctionName;

	/**
	 * APIAction constructor.
	 * @param string $apiURI
	 * @param string $successFunctionName
	 * @param string $errorFunctionName
	 */
    public function __construct(string $apiURI, $successFunctionName = "", $errorFunctionName = "")
    {
        $this->apiURI = $apiURI;

        $verify = function($name) {
            if(preg_match("/^[a-z_][a-z0-9_]*$/i", $name))
                return $name;
            throw new APIException("Invalid JS function name $name");
        };

        $this->successFunctionName = $successFunctionName ? $verify($successFunctionName) : 'undefined';
        $this->errorFunctionName = $errorFunctionName ? $verify($errorFunctionName) : 'undefined';
    }

    public function makeAction(FormElement $form)
    {
        $form["action"] = "";
        $form["onsubmit"] = preg_replace("/\s+/i", ' ', sprintf("return(function(sender, Form){try {let fd=new FormData(sender);let opts={successHandler:%s,errorHandler:%s};
const frm = Form(%s, fd, opts);
if(sender.submitted) frm.button($(sender.submitted));
} catch(e) { if(opts.errorHandler) opts.errorHandler.call(this,e); }
return false;
})(this, window.Skyline.API.Submit);", $this->getSuccessFunctionName() ?: "null", $this->getErrorFunctionName() ?: "null", var_export($this->getApiURI(), true)));
    }

    /**
     * @return string
     */
    public function getApiURI(): string
    {
        return $this->apiURI;
    }

    /**
     * @param string $apiURI
     */
    public function setApiURI(string $apiURI): void
    {
        $this->apiURI = $apiURI;
    }

    /**
     * @return mixed
     */
    public function getSuccessFunctionName()
    {
        return $this->successFunctionName;
    }

    /**
     * @param mixed $successFunctionName
     */
    public function setSuccessFunctionName($successFunctionName): void
    {
        $this->successFunctionName = $successFunctionName;
    }

    /**
     * @return mixed
     */
    public function getErrorFunctionName()
    {
        return $this->errorFunctionName;
    }

    /**
     * @param mixed $errorFunctionName
     */
    public function setErrorFunctionName($errorFunctionName): void
    {
        $this->errorFunctionName = $errorFunctionName;
    }
}