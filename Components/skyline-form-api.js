/*
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

$(function() {
    if(!window.Skyline || !window.Skyline.API)
        console.error("You need to install skyline/component-api before using the skyline/html-form-api package.");
    else {
        window.Skyline.API.Form = function(targetAPI, fd, successFunction, errorFunction) {
            return window.Skyline.API.post(targetAPI, fd)
                .success(function(data) {
                    var ok = true;

                    if(data["skyline-validation"]) {
                        for(const nam in data["skyline-validation"]) {
                            $control = $( document.getElementsByName( nam ) );
                            if($control.length > 0) {
                                var ivc = $control.attr("data-skyline-invalid");
                                var vc = $control.attr("data-skyline-ivalid");
                                if(!ivc)
                                    ivc = this.INVALID_CONTROL_CLASS;
                                if(!vc)
                                    vc = this.VALID_CONTROL_CLASS;


                                if(data["skyline-validation"][nam].valid) {
                                    $control.removeClass( ivc );
                                    $control.addClass( vc );
                                } else {
                                    ok = false;
                                    $control.removeClass( vc );
                                    $control.addClass( ivc );
                                }

                                var tag = data["skyline-validation"][nam].tag;
                                $control.parent()
                                    .removeClass("skyline-tagged-feedback")
                                    .removeClass("fb-1")
                                    .removeClass("fb-2")
                                    .removeClass("fb-3")
                                    .removeClass("fb-4")
                                    .removeClass("fb-5");

                                if(tag!=0) {
                                    $control.parent().addClass("skyline-tagged-feedback").addClass("fb-"+tag);
                                }
                            }
                        }
                    }
                    if(ok && successFunction)
                        window.setTimeout(successFunction, 1, data, true);
                    else if(!ok)
                        window.setTimeout(errorFunction, 1, data, true);
                })
                .error(function(error) {
                    if(errorFunction)
                        window.setTimeout(errorFunction, 1, error, false);
                })
        };

        window.API.Form.INVALID_CONTROL_CLASS = 'is-invalid';
        window.API.Form.VALID_CONTROL_CLASS = 'is-valid';
    }
});