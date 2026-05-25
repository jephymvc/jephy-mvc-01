<?php
namespace App\Core\GraphQL;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\FormattedError;
use App\Core\GraphQL\Config\SmartyConfigLoader;

class GraphQL
{
    private static $instance = null;
    private $queries = [];
    private $mutations = [];
    private $debug = false;
    private $config;
    private $appPath;		
	private $shield;
    
    private function __construct($appPath = null)
    {
        $this->appPath = $appPath ?? dirname(__DIR__, 3);
        $this->config = SmartyConfigLoader::getInstance($this->appPath . '/config/config.conf');
        $this->debug = filter_var($this->config->get('graphql.debug', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
    
    public static function getInstance($appPath = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($appPath);
        }
        return self::$instance;
    }
    
    public function registerQueries(array $queries): self
    {
        $this->queries = array_merge($this->queries, $queries);
        return $this;
    }
    
    public function registerMutations(array $mutations): self
    {
        $this->mutations = array_merge($this->mutations, $mutations);
        return $this;
    }
    
    public function buildSchema(): Schema
    {
        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => $this->buildFields($this->queries)
        ]);

        $mutationType = !empty($this->mutations) ? new ObjectType([
            'name' => 'Mutation',
            'fields' => $this->buildFields($this->mutations)
        ]) : null;

        return new Schema([
            'query' => $queryType,
            'mutation' => $mutationType,
            'typeLoader' => function($name) {
                return TypeRegistry::get($name);
            }
        ]);
    }
	
	private function buildFields(array $fieldConfigs): array
	{
		$fields = [];
		foreach ($fieldConfigs as $name => $config) {
			$fields[$name] = $config;
			
			// Wrap resolve with shield protection
			if ($this->shield && isset($config['resolve'])) {
				$originalResolve = $config['resolve'];
				$fields[$name]['resolve'] = function($root, $args, $context, $info) use ($name, $originalResolve) {
					$shieldResult = $this->shield->protect($name, $root, $args, $context, $info);
					
					if ($shieldResult instanceof Shield\ShieldError) {
						throw new \Exception($shieldResult->getMessage());
					}
					
					if ($shieldResult === false) {
						throw new \Exception('Access denied');
					}
					
					return $originalResolve($root, $args, $context, $info);
				};
			}
		}
		return $fields;
	}
	
    
    private function buildFieldsAlt(array $fieldConfigs): array
    {
        $fields = [];
        foreach ($fieldConfigs as $name => $config) {
            $fields[$name] = $config;
        }
        return $fields;
    }
    
    public function execute(string $query, array $variables = [], $context = null)
    {
        try {
            $schema = $this->buildSchema();
            
            $result = GraphQLBase::executeQuery(
                $schema,
                $query,
                null,
                $context,
                $variables
            );

            if ($this->debug) {
                $debug = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;
                return $result->toArray($debug);
            }

            return $result->toArray();
            
        } catch (\Exception $e) {
            return [
                'errors' => [FormattedError::createFromException($e)]
            ];
        }
    }

	public function setShield(Shield $shield): self
	{
		$this->shield = $shield;
		return $this;
	}

	
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }
}

