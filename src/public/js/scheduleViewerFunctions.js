let createPopover  = (element) => {
    element = $(element);

    let htmlEncode = (value) => {
        return $("<div/>").text(value).html();
    };

    let coursenum = htmlEncode(element.data('coursenum'));
    let fos = htmlEncode(element.data('fos'));
    let prof = htmlEncode(element.data('prof'));
    let crns = element.data('crns');
    let coursetitle = htmlEncode(element.data('coursetitle'));
    let html = '<p> ' + fos + ' ' + coursenum + ' with CRN: ' + crns + '</p><p>Professor: ' + prof + '</p>';
    if (element.data('prereg') == "1") {
        html = html + "<p>You have already registered for this course</p>";
    }
    let options = {placement: 'bottom', container: "body", trigger: 'manual', html: true, title: coursetitle};
    element.data('content', html).popover(options);
};

$('table')
    .on('mouseenter', 'td.has-data', (e) => {
        let td = $(e.target);
        setTimeout(() => {
            td.popover('show');
        }, 200);
    })
    .on('mouseleave', 'td.has-data', (e) => {
        let td = $(e.target);
        setTimeout(() => {
            if (!$(".popover:hover").length) {
                $(td).popover('hide');
            }
        }, 200);
    });

$('td.has-data').toArray().forEach(createPopover);

$(document).ready(() => {
    $('[data-toggle="tooltip"]').tooltip();

    $(document).on('mouseleave', ".popover", (e) => {
        setTimeout(() => {
            if (!$(".popover:hover").length) {
                $(e.target).closest(".popover").remove();
            }
        }, 300);
    });
});
