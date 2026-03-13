<?php
namespace App\Core;
abstract class Controller
{
    
	protected $smarty;
    protected $hooks;
    protected $globalData;
    
    public function __construct()
    {
        $this->smarty 		= Framework::getSmarty();
        $this->hooks 		= Framework::getHooks();
		$this->globalData 	= Framework::getGlobalDataHook();
    }
    
    protected function render( $template, $data = [])
    {
		
		//	echo '<pre>';
		//	print_r( $data );
		//	echo '</pre>';
		
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        
        // Execute hooks before rendering
        $this->hooks->exec( 'beforeRender', [
            'template' => $template,
            'data' => $data
        ]);
        
        echo $this->smarty->fetch($template);
    }
	
	/**
     * Render template with hook support (returns string)
     */
    protected function fetch($template, $data = [])
    {
        
		foreach ( $data as $key => $value ) {
            $this->smarty->assign( $key, $value );
        }
        
        // Execute beforeRender hook
        $this->hooks->exec( 'beforeRender', [
            'template' 	=> $template,
            'data' 		=> $data
        ] );
        
        // Fetch template content
        $content = $this->smarty->fetch($template);
        
        // Execute afterRender hook
        $content = $this->hooks->exec('afterRender', [
            'template' 	=> $template,
            'data' 		=> $data,
            'content' 	=> $content
        ])['content'] ?? $content;
        
        return $content;
		
    }
	
	/**
     * Display template using Smarty's display() with hook support
     */
    protected function display($template, $data = [])
    {
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        
        // Execute beforeRender hook
        $this->hooks->exec('beforeRender', [
            'template' => $template,
            'data' => $data
        ]);
        
        // Start output buffering
        ob_start();
        
        // Let Smarty output directly
        $this->smarty->display($template);
        
        // Get the output
        $output = ob_get_contents();
        ob_end_clean();
        
        // Execute afterRender hook
        $output = $this->hooks->exec('afterRender', [
            'template' => $template,
            'data' => $data,
            'output' => $output
        ])['output'] ?? $output;
        
        echo $output;
		
    }
	
	/**
     * Quick render without hooks (for performance)
     */
    protected function quickRender($template, $data = [])
    {
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        
        $this->smarty->display($template);
    }
    
    protected function json($data)
    {		
		header('Content-Type: application/json');
		echo json_encode($data);
        exit;
    }
    
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
    
    // ==================== NEW VALIDATION METHODS ====================
    
    protected function validate($data, $rules)
    {
		
		
        $validator = new Validator($data);
        $validator->make($data, $rules);
        
        if (!$validator->validate()) {
            $this->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
            exit;
        }
        
        return $validator->validated();
    }
    
    protected function validateWithRedirect($data, $rules, $redirectUrl)
    {
        $validator = new Validator($data);
        $validator->make($data, $rules);
        
        if (!$validator->validate()) {
            // Store errors and old input in session
            $_SESSION['validation_errors'] = $validator->errors();
            $_SESSION['old_input'] = $data;
            
            // Add flash message
            if (class_exists('GlobalDataHook')) {
                GlobalDataHook::addFlash('error', 'Please fix the errors below.');
            }
            
            $this->redirect($redirectUrl);
            exit;
        }
        
        return $validator->validated();
    }
    
    protected function sanitizeInput($input, $rules = [])
    {
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $this->sanitizeValue($value, $rules[$key] ?? '');
            }
        }
        
        return $sanitized;
    }
    
    private function sanitizeArray($array)
    {
        $sanitized = [];
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $this->sanitizeValue($value);
            }
        }
        
        return $sanitized;
    }
    
    private function sanitizeValue($value, $rule = '')
    {
        if (is_numeric($value)) {
            return $value;
        }
        
        // Apply rule-specific sanitization
        if (!empty($rule)) {
            $validator = new Validator();
            $rules = explode('|', $rule);
            
            foreach ($rules as $r) {
                $value = $validator->applySanitization('', $value, $r);
            }
        }
        
        // Default sanitization
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        return $value;
    }
    
    protected function getValidationErrors()
    {
        return $_SESSION['validation_errors'] ?? [];
    }
    
    protected function getOldInput($field = null, $default = '')
    {
        if ($field === null) {
            return $_SESSION['old_input'] ?? [];
        }        
        return $_SESSION['old_input'][$field] ?? $default;
    }
    
    protected function setSession($field = null, $default = '')
    {           
       $_SESSION[$field]= $default;
    }
    
	protected function getSession($field = null, $default = '')
    {     
		if( isset( $_SESSION[$field] ) ){
			return $_SESSION[$field];
		}else{
			return $default;
		}
        
    }
    
    protected function clearValidation()
    {
        unset($_SESSION['validation_errors'], $_SESSION['old_input']);
    }
	
    protected function hashPassword( $password )
    {
       return password_hash( $password, PASSWORD_DEFAULT );
    }
	
    protected function passwordhash( $password )
    {
       return password_hash( $password, PASSWORD_DEFAULT );
    }
	
	
	protected function isLoggedIn( $session_key )
    {
       return isset( $_SESSION[ "user_session" ][ $session_key ] );
    }
	
	
	protected function loginUser( $session_key, $userData )
    {
		if( !isset( $_SESSION[ "user_session" ][ $session_key ] ) ){
			$encryptedSessionData 						= $this->AESEncrypt( implode( ";", $userData ), $this->globalData->get( 'app.encryption_key' ) );
			$_SESSION[ "user_session" ][ $session_key ] = $encryptedSessionData;
		}
				
    }
	
	protected function logoutUser( $session_key, $redirectUrl = "" )
    {
		if( isset( $_SESSION[ "user_session" ][ $session_key ] ) ){
			$_SESSION[ "user_session" ][ $session_key ] = "";
			unset( $_SESSION[ "user_session" ][ $session_key ] );
			$redirect = $redirectUrl == "" ? "/" : $redirectUrl;
			header( "location:" .$redirect );
		}	
    }
	
	protected function getLoginData( $session_key )
    {
		$loginData = $this->AESDecrypt( $_SESSION[ "user_session" ][ $session_key ], $this->globalData->get( 'app.encryption_key' ) );		
		return explode( ";", $loginData );
	}
	
	
	/**
	 * crypt AES 256
	 *
	 * @param data $data
	 * @param string $password
	 * @return base64 encrypted data
	 */
	protected function AESEncrypt($data, $password) 
	{
		// Set a random salt
		$salt = openssl_random_pseudo_bytes(16);

		$salted = '';
		$dx = '';
		// Salt the key(32) and iv(16) = 48
		while (strlen($salted) < 48) {
		  $dx = hash('sha256', $dx.$password.$salt, true);
		  $salted .= $dx;
		}

		$key = substr($salted, 0, 32);
		$iv  = substr($salted, 32,16);

		$encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, true, $iv);
		return base64_encode($salt . $encrypted_data);
	}
	
	
	 /**
	  * decrypt AES 256
	  *
	  * @param data $edata
	  * @param string $password
	  * @return decrypted data
	  */
	  
	protected function AESDecrypt($edata, $password) 
	{
		$data 	= base64_decode($edata);
		$salt 	= substr($data, 0, 16);
		$ct 	= substr($data, 16);

		$rounds = 3; // depends on key length
		$data00 = $password.$salt;
		$hash = array();
		$hash[0] = hash('sha256', $data00, true);
		$result = $hash[0];
		for ($i = 1; $i < $rounds; $i++) {
			$hash[$i] = hash('sha256', $hash[$i - 1].$data00, true);
			$result .= $hash[$i];
		}
		$key = substr($result, 0, 32);
		$iv  = substr($result, 32,16);

		return openssl_decrypt($ct, 'AES-256-CBC', $key, true, $iv);
	}
	
	/**
	 * Create a URL-friendly slug from a string
	 * 
	 * @param string $string The input string to convert
	 * @param string $separator Word separator (default: '-')
	 * @param bool $lowercase Convert to lowercase (default: true)
	 * @param string $language Language for transliteration (default: 'en')
	 * @return string URL-friendly slug
	 */
	protected function createSlug(string $string, string $separator = '-', bool $lowercase = true, string $language = 'en'): string
	{
		// Handle empty input
		if (empty($string)) {
			return '';
		}
		
		// Step 1: Transliterate non-ASCII characters
		$string = $this->transliterate($string, $language);
		
		// Step 2: Convert to lowercase if requested
		if ($lowercase) {
			$string = mb_strtolower($string, 'UTF-8');
		}
		
		// Step 3: Replace non-alphanumeric characters with separator
		$string = preg_replace('/[^\p{L}\p{N}]+/u', $separator, $string);
		
		// Step 4: Trim separators from beginning and end
		$string = trim($string, $separator);
		
		// Step 5: Remove duplicate separators
		$string = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $string);
		
		return $string;
	}
	
	/**
	 * Transliterate international characters to ASCII
	 */
	protected function transliterate(string $string, string $language = 'en'): string
	{
		// If intl extension is available, use Transliterator
		if (extension_loaded('intl')) {
			$transliterator = transliterator_create('Any-Latin; Latin-ASCII');
			$string = transliterator_transliterate($transliterator, $string);
			return $string;
		}
		
		// Fallback: Manual transliteration map
		$transliterationMap = [
			// German umlauts
			'Ä' => 'Ae', 'ä' => 'ae',
			'Ö' => 'Oe', 'ö' => 'oe',
			'Ü' => 'Ue', 'ü' => 'ue',
			'ß' => 'ss',
			
			// French accents
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
			'Ç' => 'C', 'ç' => 'c',
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
			'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'Ñ' => 'N', 'ñ' => 'n',
			'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
			'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
			'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U',
			'ù' => 'u', 'ú' => 'u', 'û' => 'u',
			
			// Scandinavian
			'Å' => 'Aa', 'å' => 'aa',
			'Ø' => 'Oe', 'ø' => 'oe',
			'Æ' => 'Ae', 'æ' => 'ae',
			
			// Greek (basic)
			'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd',
			'ε' => 'e', 'ζ' => 'z', 'η' => 'i', 'θ' => 'th',
			'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm',
			'ν' => 'n', 'ξ' => 'x', 'ο' => 'o', 'π' => 'p',
			'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y',
			'φ' => 'f', 'χ' => 'ch', 'ψ' => 'ps', 'ω' => 'o',
			
			// Russian/Cyrillic (basic)
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g',
			'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
			'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
			'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
			'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
			'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '',
			'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
			'я' => 'ya',

		];
		
		return strtr($string, $transliterationMap);
	}
	
	
	protected function isSubdomain(string $domain): bool
	{
		// Remove protocol and path
		$domain = parse_url($domain, PHP_URL_HOST) ?? $domain;
		
		// Remove www. prefix
		$domain = preg_replace('/^www\./', '', $domain);
		
		// Split by dots
		$parts = explode('.', $domain);
		
		// A domain needs at least 2 parts to be valid (example.com)
		// If it has 3 or more parts, it's likely a subdomain
		// But we need to handle special cases like .co.uk, .com.au etc.
		return count($parts) > 2;
	}
		
	
}

