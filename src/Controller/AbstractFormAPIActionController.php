<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Skyline\HTML\Form\Controller;


use Skyline\API\Controller\AbstractAPIActionController;
use Skyline\API\Render\JSONRender;
use Skyline\HTML\Form\Control\AbstractControl;
use Skyline\HTML\Form\Control\ActionControlInterface;
use Skyline\HTML\Form\Control\Button\ActionButtonControl;
use Skyline\HTML\Form\FormData;
use Skyline\HTML\Form\FormElement;
use Skyline\Kernel\Service\SkylineServiceManager;
use Skyline\Render\Info\RenderInfoInterface;
use Skyline\Router\Description\ActionDescriptionInterface;
use Symfony\Component\HttpFoundation\Response;
use TASoft\DI\Injector\CallbackInjector;

abstract class AbstractFormAPIActionController extends AbstractAPIActionController
{
    use FormAPITrait;

    public function performAction(ActionDescriptionInterface $actionDescription, RenderInfoInterface $renderInfo)
    {
        if(!$this->isPreflightRequest($this->request)) {
            $renderInfo->set( RenderInfoInterface::INFO_PREFERRED_RENDER, JSONRender::RENDER_NAME);

            $response = $this->response;
            if($response instanceof Response) {
            	$response->headers->set("Content-Type", 'application/json');
			}
        }
        return parent::performAction($actionDescription, $renderInfo);
    }

    /**
     * Your API controller should call this method to generate model entries describing, which elements are valid and which are invalid.
     * The Skyline CMS API-Form component knows how to handle and marks the elements as valid or invalid.
     * If the form is valid and verified, this method returns true, otherwise false.
     * In addition, it will update the API data model with the validation status.
     *
     * @param FormElement $element
     */
    protected function writeValidationToModel(FormElement $element) {
        if($element->isValidated()) {
            foreach($element->getChildElements() as $control) {
                if($control instanceof AbstractControl && !($control instanceof ActionControlInterface)) {
                    $this->writeRawValidationToModel(
                        $control->getName(),
                        $control->isValid(),
                        method_exists($control->getStoppedValidator(), 'getTag') ? $control->getStoppedValidator()->getTag() : 0
                    );
                }
            }
        }
    }

    /**
     * Checks the form and if valid, performs the action sent by post.
     * This method will perform the action under dependency injection.
     *
     * @param FormElement $form
     * @return bool
     */
    protected function performFormAction(FormElement $form) {
        $request = $this->request;

        if($form->isValidated() && $form->isValid()) {
            foreach($form->getActionControls() as $control) {
                if($request->request->has($control->getName())) {
                    $dm = SkylineServiceManager::getDependencyManager();
                    return $dm->pushGroup(function() use ($dm, $form, $control) {
                        $dm->addDependencyInjector(new CallbackInjector(function($type, $name) use ($form) {
                            if($name == 'data' || $name == 'formData')
                                return new FormData($form->getData());
                            if($type == FormData::class)
                                return new FormData($form->getData());
                            return NULL;
                        }));

                        $cb = [$control, 'performAction'];
                        if($control instanceof ActionButtonControl)
                            $cb = $control->getActionCallback();

                        return $dm->call($cb);
                    });
                }
            }
        }
        return false;
    }
}