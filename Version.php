<?php
/**
 * Version Class
 * @Author: roni.cse@gmail.com
 * @link: http://github.com/xiidea/version
 */

class Version
{
    CONST DEFAULT_MAJOR = 0;
    CONST DEFAULT_MINOR = 0;
    CONST MODIFIER_SEPARATOR = '-';
    private static $modifierRegex = '[._-]?(?:([a-z A-Z]+)(?:[.-]?(\d+))?)?';

    private $_value = array(
        'major' => self::DEFAULT_MAJOR,
        'minor' => self::DEFAULT_MINOR,
        'patch' => '',
    );

    private $_modifier = '';

    public function __construct($version = null)
    {
        if ($version !== null) {
            $this->parse($version);
        }
    }

    public function parse($version)
    {
        $version = trim($version);

        if (preg_match('{^v?(\d{1,3})(\.\d+)?(\.\d+)?' . self::$modifierRegex . '$}i', $version, $matches)) {
            $this->_value['major'] = $matches[1];
            $this->_value['minor'] = ltrim($matches[2], '.');
            $this->_value['patch'] = ltrim($matches[3], '.');
            $index = 4;
        } elseif (preg_match('{^v?(\d{4}(?:[.:-]?\d{2}){1,6}(?:[.:-]?\d{1,3})?)' . self::$modifierRegex . '$}i', $version, $matches)) { // match date-based versioning
            $this->_value['major'] = preg_replace('{\D}', '-', $matches[1]);
            $this->_value['minor'] = $this->_value['patch'] = '';
            $index = 2;
        }

        if (isset($index)) {
            if (!empty($matches[$index])) {
                $this->_modifier = $matches[$index] . (!empty($matches[$index+1]) ? $matches[$index+1] : '');
            }

            return $this;
        }

        throw new \UnexpectedValueException('Invalid version string "' . $version . '"');
    }

    public function nextMajor()
    {
        $this->_value['major']++;

        return $this;
    }

    public function nextMinor()
    {
        $this->_value['minor']++;

        return $this;
    }

    public function nextPatch()
    {
        $this->_value['patch']++;

        return $this;
    }

    public function setModifier($modifier = '')
    {
        $this->_modifier = $modifier;

        return $this;
    }

    public function nextModifier()
    {
        preg_match('{\d}', $this->_modifier, $matches);
        if(!isset($matches[0])){
            $this->_modifier = $this->_modifier . '1';
        }else{
            $this->_modifier = preg_replace('{\d}', $matches[0]+1, $this->_modifier);
        }

        return $this;
    }

    /**
     * Normalizes a version string to be able to perform comparisons on it
     *
     * @throws \UnexpectedValueException
     * @return array
     */
    public function normalize()
    {
        if (preg_match('{^v?(\d{4}(?:[.:-]?\d{2}){1,6}(?:[.:-]?\d{1,3})?)' . self::$modifierRegex . '$}i', $this->_value['major'], $matches)) { // match date-based versioning
            $version = preg_replace('{\D}', '-', $matches[1]);
        } else {
            $version = implode('.', array_map('intval', $this->_value));
        }

        if (!empty($this->_modifier)) {
            $version .= self::MODIFIER_SEPARATOR . $this->_modifier;
        }

        return $version;
    }

    public function __toString()
    {
        $version = implode('.', array_filter($this->_value));

        if (!empty($this->_modifier)) {
            $version .= self::MODIFIER_SEPARATOR . $this->_modifier;
        }

        return $version;
    }
}