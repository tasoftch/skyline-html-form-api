


((API, $) => {
    let _defaults = {
        successHandler: () => {},
        errorHandler: () => {},
        parentHandler: ($ctl) => {return $ctl.parent();},
        validClass: ()=>{return API.Submit.VALID_CONTROL_CLASS},
        invalidClass:()=>{return API.Submit.INVALID_CONTROL_CLASS},
    };

    if(!API)
        throw "Can not load Skyline Form API without Skyline API component loaded before.";

    API.Submit = (targetAPI, formData, options) => {
        options = $.extend({}, _defaults, options);

        return API.post(targetAPI, formData)
            .success((data) => {
                let ok = true;

                if(data["skyline-validation"]) {
                    let feedbacks = data["skyline-validation"];

                    for(const nam in feedbacks) {
                        if(feedbacks.hasOwnProperty(nam)) {
                            let $control = $( document.getElementsByName( nam ) );
                            if($control.length > 0) {
                                var ivc = $control.attr("data-invalid-class");
                                var vc = $control.attr("data-valid-class");
                                if(!ivc) {
                                    if(typeof options.invalidClass === 'function')
                                        ivc = options.invalidClass.call(API.Submit);
                                    else if(options.invalidClass)
                                        ivc = options.invalidClass;
                                    else
                                        ivc = API.Submit.INVALID_CONTROL_CLASS;
                                }

                                if(!vc) {
                                    if(typeof options.validClass === 'function')
                                        vc = options.validClass.call(API.Submit);
                                    else if(options.validClass)
                                        vc = options.validClass;
                                    else
                                        vc = API.Submit.VALID_CONTROL_CLASS;
                                }

                                let response = feedbacks[nam];
                                if(response.valid) {
                                    $control.removeClass( ivc );
                                    $control.addClass( vc );
                                } else {
                                    ok = false;
                                    $control.removeClass( vc );
                                    $control.addClass( ivc );
                                }

                                let tag = response.tag;
                                let $parent = parentHandler($control);

                                $parent.removeClass("skyline-tagged-feedback");
                                for(let e=1;e<=10;e++)
                                    $parent.removeClass("fb-"+e);

                                if(tag !== 0)
                                    $parent.addClass("skyline-tagged-feedback").addClass("fb-"+tag);
                            }
                        }
                    }
                }

                if(ok)
                    window.setTimeout(options.successHandler, 1, data, true);
                else
                    window.setTimeout(options.errorHandler, 1, data, true);
            })
            .error((err) => {
                window.setTimeout(options.errorHandler, 1, err, false);
            });
    };

    API.Submit.INVALID_CONTROL_CLASS = 'is-invalid';
    API.Submit.VALID_CONTROL_CLASS = 'is-valid';
})(window.Skyline && window.Skyline.API ? window.Skyline.API : undefined, window.jQuery);