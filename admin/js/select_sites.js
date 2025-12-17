$(document).ready(function(){

    $('#service_type').on('change', function () {

        let serviceType = $(this).val();

        if (serviceType === "") {
            $('#sitename').html('<option value="">-- Select Service First --</option>');
            return;
        }

        $.ajax({
            url: 'get_sites.php',
            type: 'GET',
            data: { service_type: serviceType },
            dataType: 'json',
            success: function(data) {
                let options = '<option value="">-- Select Site --</option>';
                data.forEach(function(site) {
                    options += `<option value="${site.site_id}">${site.site_name}</option>`;
                });
                $('#sitename').html(options);
            },
            error: function(xhr, status, error) {
                $('#sitename').html('<option>Error loading sites</option>');
                console.error(error);
            }
        });

    });

});


