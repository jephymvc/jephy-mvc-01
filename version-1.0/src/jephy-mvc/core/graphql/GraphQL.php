<?php
namespace App\Core\GraphQL;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\FormattedError;
use GraphQL\Error\Error;
use App\Core\GraphQL\Config\SmartyConfigLoader;
use App\Core\GraphQL\Shield;
use App\Core\GraphQL\Shield\ShieldError;

class GraphQL
{
    private static $instance = null;
    private $queries = [];
    private $mutations = [];
    private $debug = false;
    private $config;
    private $appPath;		
    private $shield = null; // Initialize as null
    
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

        $schemaConfig = ['query' => $queryType];
        
        if ($mutationType) {
            $schemaConfig['mutation'] = $mutationType;
        }
        
        // Only add typeLoader if TypeRegistry exists
        if (class_exists('App\Core\GraphQL\TypeRegistry')) {
            $schemaConfig['typeLoader'] = function($name) {
                return TypeRegistry::get($name);
            };
        }

        return new Schema($schemaConfig);
    }
    
    private function buildFields(array $fieldConfigs): array
    {
        $fields = [];
        foreach ($fieldConfigs as $name => $config) {
            $fields[$name] = $config;
            
            // Wrap resolve with shield protection
            if ($this->shield !== null && isset($config['resolve'])) {
                $originalResolve = $config['resolve'];
                $shield = $this->shield; // Store reference for closure
                
                $fields[$name]['resolve'] = function($root, $args, $context, $info) use ($name, $originalResolve, $shield) {
                    $shieldResult = $shield->protect($name, $root, $args, $context, $info);
                    
                    if ($shieldResult instanceof ShieldError) {
                        throw new Error($shieldResult->getMessage());
                    }
                    
                    if ($shieldResult === false) {
                        throw new Error('Access denied');
                    }
                    
                    return $originalResolve($root, $args, $context, $info);
                };
            }
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
            error_log('GraphQL Error: ' . $e->getMessage());
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