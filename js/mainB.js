$(document).ready(function()
{
    $('#myTab a').click(function (e) {
        //e.preventDefault() // Аналог "return false" или типа того
        $(this).tab('show')
    });
    $('#myMenu a').click(function (e) {
        //e.preventDefault() // Аналог "return false" или типа того
        $(this).tab('show')
    });
    //$('#myPopover').popover(options);
});
