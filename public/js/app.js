/**
 * Add jQuery Validation plugin method for a valid password
 * 
 * Valid password contain at least one letter and number
 */

$.validator.addMethod('validPassword', function(value, element, param){
    if( value != ''){
       if (value.match(/.*[a-z]+.*/i) === null){
           return false;
       }
       if (value.match(/.*\d+.*/i) === null){
           return false;
       }
    }
    return true;
}, 'Must contain at least one letter and number' );