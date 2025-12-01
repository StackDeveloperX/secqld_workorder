$(document).ready(function(){
    $.ajax({
        url: 'get_sites.php',
        type: 'GET',
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

$(document).ready(function(){
    $.ajax({
        url: 'get_sites.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            let options = '<option value="">-- Select Site --</option>';
            data.forEach(function(site) {
                options += `<option value="${site.site_id}">${site.site_name}</option>`;
            });
            $('#sitename_recurring').html(options);
        },
        error: function(xhr, status, error) {
            $('#sitename_recurring').html('<option>Error loading sites</option>');
            console.error(error);
        }
    });
});