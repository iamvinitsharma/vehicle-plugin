jQuery( document ).ready( function($) 
{
    jQuery('#vehicle_type').on('change', function()
    {
        var vehicle_type = this.value;
        jQuery.ajax({
            type: 'post',
            url: AjaxUrl.ajaxurl,
            data: {
                action: 'get_vehicle_list',
                vehicle_type: vehicle_type
            },
            success: function( result ) 
            {
            	console.log(result);
            	jQuery("#vehicle").html(result);
            }
        })
        return false;
    });

    jQuery('#vehicle').on('change', function()
    {
        //alert('hello');
        var vehicle = this.value;
        jQuery.ajax({
            type: 'post',
            url: AjaxUrl.ajaxurl,
            data: {
                action: 'get_vehicle_price',
                vehicle: vehicle
            },
            success: function( result ) 
            {
                console.log(result);
                jQuery("#vehicle_price").val(result);
				alert(result);
            }
        })
        return false;
    });
})