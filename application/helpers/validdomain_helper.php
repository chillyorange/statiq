<?php
function is_valid_domain_name($domain_name) {

    if(filter_var(gethostbyname($domain_name), FILTER_VALIDATE_IP)) {
    
        return true;
    
    } else {
    
    	return false;
    
    }
}
?>