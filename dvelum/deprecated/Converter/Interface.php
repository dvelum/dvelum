<?php
interface Converter_Interface{
  /**
   * Convert value
   * @param mixed $value
   * @throws Exception
   * @return mixed
   */
  static public function convert($value);
}