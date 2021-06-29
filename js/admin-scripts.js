jQuery( document ).ready( function($) 
{
    $('#vehicle_type').on('change', function()
    {
        var vehicle_type = this.value;
        $.ajax({
            type: 'post',
            url: AjaxUrl.ajaxurl,
            data: {
                action: 'get_vehicle_list',
                vehicle_type: vehicle_type
            },
            success: function( result ) 
            {
                console.log(result);
                $("#vehicle").html(result);
            }
        })
        return false;
    });
})