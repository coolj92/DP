;(function ($) {
    'use strict';

    $(function () {
        setTimeout(function () {
            $(".at_biz_dir-linking_type").select2({
                allowClear: true,
                tags: false,
                maximumSelectionLength: 100,
                width: '100%',
                tokenSeparators: [","],
                ajax: {
                    url: DIRLINK.ajaxUrl,
                    data: function (params) {
                        var query = {
                            search: params.term,
                            type: $(this).attr("data-type"),
                            action: DIRLINK.action
                        }

                        return query;
                    },
                    processResults: function (response) {
                        if (!response.success) {
                            response.data = [{
                                id: -1,
                                text: 'Not found',
                            }];
                        }

                        return {
                            results: response.data
                        };
                    }
                }
            });
        }, 1000);

        $(".at_biz_dir-linking_type").select2({
            width: '100%',
            ajax: {
                url: DIRLINK.ajaxUrl,
                data: function (params) {
                    var query = {
                        action: DIRLINK.action,
                        type: $(this).attr("data-type"),
                        search: params.term,
                    }

                    return query;
                },
                processResults: function (response) {
                    if (!response.success) {
                        response.data = [{
                            id: -1,
                            text: 'Not found',
                        }];
                    }

                    return {
                        results: response.data
                    };
                }
            }
        });

        $('body').on('change', 'select[name="directory_type"]', function () {
            setTimeout(function () {
                $(".at_biz_dir-linking_type").select2({
                    width: '100%',
                    ajax: {
                        url: DIRLINK.ajaxUrl,
                        data: function (params) {
                            var query = {
                                search: params.term,
                                type: $(this).attr("data-type"),
                                action: DIRLINK.action
                            }

                            return query;
                        },
                        processResults: function (response) {
                            if (!response.success) {
                                response.data = [{
                                    id: -1,
                                    text: 'Not found',
                                }];
                            }

                            return {
                                results: response.data
                            };
                        }
                    }
                });
            }, 1000);
        });

    });
}(jQuery));