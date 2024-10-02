<?php

    function verifyInjection($formElements){
        $elements = $formElements;
		$characters = ['"', "\\", chr(0)];
		$specialCharacters = false;
		foreach($elements as $elem){
			$_POST[$elem] = str_replace("'","''",$_POST[$elem]);
			foreach($characters as $char)
				$specialCharacters |= is_numeric(strpos($_POST[$elem], $char));
		}
        return $specialCharacters;

    }
