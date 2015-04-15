<?php
class Sysdocs_Analyzer
{
    /**
     * @var string
     */
    protected $className;
    /**
     * @var ReflectionClass
     */
    protected $reflectionClass;

    public function __construct($className)
    {
        $this->className = $className;
        $this->reflectionClass = new ReflectionClass($className);
    }

    /**
     * Checks if class is abstract
     * @return boolean
     */
    public function isAbstract()
    {
      return $this->reflectionClass->isAbstract();
    }

    /**
     * Get current object type
     * @return string  - class/trait/interface
     */
    public function getType()
    {
      if($this->reflectionClass->isInterface())
          return 'interface';

      if(method_exists($this->reflectionClass , 'isTrait') && $this->reflectionClass->isTrait())
          return 'trait';

      return 'class';
    }
    /**
     * Get parent class name
     * @return string
     */
    public function getExtends()
    {
      $parent = $this->reflectionClass->getParentClass();

      if($parent)
          return $parent->getName();

      return '';
    }
    /**
     * Get implemented interface names
     * @return string
     */
    public function getImplements()
    {
        $i = $this->reflectionClass->getInterfaceNames();

        if(!empty($i))
            return implode(', ', $i);

        return '';
    }
    /**
     * Gets namespace name
     * @return string
     */
    public function getNamespace()
    {
      return $this->reflectionClass->getNamespaceName();
    }
    /**
     * Gets doc comments
     * @return string
     */
    public function getComment()
    {
      return $this->clearComment($this->reflectionClass->getDocComment());
    }
    /**
     * Clear comment string
     * @param string $string
     * @return string
     */
    protected function clearComment($string)
    {
        if(strlen($string)){
           $string = str_replace(array('/**','/*','*/','*',"\t",'  '), '', $string);
        }else{
           $string = '';
        }
        return $string;
    }
    /**
     * Try to find PHPDoc param
     * @param string $string
     */
    public function exctractPhpDocParam($param, $string)
    {
        if(empty($string))
            return '';

        $lines = explode("\n", $string);

        foreach ($lines as $line)
        {
           $pos = strpos($line, $param);
           if($pos !==false){
             return trim(substr($line, ($pos + strlen($param))));
           }
        }
        return '';
    }
    /**
     * Try to find PhpDoc function param
     * @param string $paramName
     * @param string $string
     * @return string
     */
    public function exctractPhpDocFunctionArgument($paramName , $string)
    {
        if(empty($string))
            return '';

        $lines = explode("\n", $string);

        foreach ($lines as $line)
        {
            $pos = strpos($line, '@param');

            if($pos === false)
                continue;

            $line = substr($line, strlen('@param'));
            $pos = strpos($line, '$'.$paramName);

            if(
                 $pos !==false
                 &&
                (
                    strlen($line) == ($pos + strlen('$'.$paramName))
                     ||
                    $line[$pos + strlen('$'.$paramName)] == ' '
                )
             )
            {
              return trim(str_replace('$'.$paramName, '', $line));
            }
        }

        return '';
    }

    /**
     * Check if class is deprecated
     * @return boolean
     */
    public function isDeprecated()
    {
      if(strpos($this->getComment(), '@deprecated') !==false)
          return true;

      return false;
    }
    /**
     * Gets constants
     * @return array
     */
    public function getConstants()
    {
      $data = array();
      $constants = $this->reflectionClass->getConstants();

      if(empty($constants))
          return array();

      $parentClass = $this->reflectionClass->getParentClass();

      foreach ($constants as $name=>$value)
      {
        $inherited = false;

        if($parentClass && $parentClass->hasConstant($name)){
            $inherited = true;
        }

        $data[] = array(
          'name' => $name,
          'value' => $value,
          'inherited' => $inherited
        );
      }

      return $data;
    }
    /**
     * Gets properties
     * @return array
     */
    public function getProperties()
    {
      $data = array();
      $properties = $this->reflectionClass->getProperties();

      if(empty($properties))
          return false;

      $parentClass = $this->reflectionClass->getParentClass();

      foreach ($properties as $property)
      {
         $comment = $this->clearComment($property->getDocComment());
         $deprecated = false;
         $inherited = false;

         if($this->reflectionClass->getName() !== $property->getDeclaringClass()->getName()){
             $inherited = true;
         }

         if(strpos($comment, '@deprecated') !==false)
             $deprecated = true;

         $data[] = array(
             'deprecated'=>$deprecated,
             'name'=>$property->getName(),
             'description'=>$comment,
             'static' => $property->isStatic(),
             'type' => $this->exctractPhpDocParam('@var' , $comment),
             'visibility'=>$this->extractVisibility($property),
             'inherited'=>$inherited
         );
      }
      return  $data;
    }
    /**
     * Get visibillity
     * @param ReflectionProperty|ReflectionMethod $reflection
     * @return string
     */
    protected function extractVisibility($reflection)
    {
       if($reflection->isPublic())
           return 'public';

       if($reflection->isPrivate())
           return 'private';

       if($reflection->isProtected())
           return 'protected';

        return '';
    }
    /**
     * Gets methods
     * @return array
     */
    public function getMethods()
    {
        $data = array();
        $methods = $this->reflectionClass->getMethods();

        if(empty($methods))
            return false;

        $parentClass = $this->reflectionClass->getParentClass();

        foreach ($methods as $method)
        {
            $comment = $this->clearComment($method->getDocComment());
            $deprecated = false;
            $inherited = false;

            if(strpos($comment, '@deprecated') !==false)
                $deprecated = true;

            if($this->reflectionClass->getName() !== $method->getDeclaringClass()->getName()){
                $inherited = true;
            }

            $data[] = array(
                'name'=> $method->getName(),
                'returnType'=>$this->exctractPhpDocParam('@return' , $comment),
                'deprecated'=>$deprecated,
                'description'=>$comment,
                'abstract'=>$method->isAbstract(),
                'throws'=>$this->exctractPhpDocParam('@throws' , $comment),
                'static'=>$method->isStatic(),
                'visibility'=>$this->extractVisibility($method),
                'final'=>$method->isFinal(),
                'inherited'=>$inherited,
                'returnsReference'=>$method->returnsReference()
            );
        }
        return  $data;
    }
    /**
     * Get method params
     * @param string $method
     * @return array
     */
    public function getParametrs($method)
    {
        $method = $this->reflectionClass->getMethod($method);
        $comment = $this->clearComment($method->getDocComment());
        $params = $method->getParameters();

        if(empty($params))
            return array();

        $data = array();

        foreach ($params as $param)
        {
            $default = null;
            if($param->isDefaultValueAvailable())
            {
               if($param->isDefaultValueConstant()){
                   $default = $param->getDefaultValueConstantName();
               }else{
                   $default = $param->getDefaultValue();
               }

               if(!is_string($default) && !is_numeric($default))
                $default = var_export($default , true);
            }


            $data[] = array(
	         'name'   => $param->getName(),
	         'index'  => $param->getPosition(),
	         'default'  => $default,
	         'isRef'  => $param->isPassedByReference(),
	         'description'  => $this->exctractPhpDocFunctionArgument($param->getName(), $comment),
             'optional' => $param->isOptional(),
            );
        }
        return $data;
    }
}