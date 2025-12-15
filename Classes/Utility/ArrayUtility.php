<?php
namespace JambageCom\Div2007\Utility;

/**
 * Class with helper functions for array handling
 */
class ArrayUtility
{
    /**
     *
     * author: jthompson/arrayDifference.php
     *
     * https://gist.github.com/cjthompson/5485005
     *
     * Compare two arrays and return a list of items only in array1 (deletions) and only in array2 (insertions)
     *
     * @param array $array1 The 'original' array, for comparison. Items that exist here only are considered to be deleted (deletions).
     * @param array $array2 The 'new' array. Items that exist here only are considered to be new items (insertions).
     * @param array $keysToCompare A list of array key names that should be used for comparison of arrays (ignore all other keys)
     * @return array[] array with keys 'insertions' and 'deletions'
    */
    public static function arrayDifference(array $array1, array $array2, array $keysToCompare = null) {

        $serialize = function (&$item, $idx, $keysToCompare) {
            if (is_array($item) && $keysToCompare) {
                $a = array();
                foreach ($keysToCompare as $k) {
                    if (array_key_exists($k, $item)) {
                        $a[$k] = $item[$k];
                    }
                }
                $item = $a;
            }
            $item = serialize($item);
        };

        $deserialize = function (&$item) {
            $item = unserialize($item);
        };

        array_walk($array1, $serialize, $keysToCompare);
        array_walk($array2, $serialize, $keysToCompare);

        // Items that are in the original array but not the new one
        $deletions = array_diff($array1, $array2);
        $insertions = array_diff($array2, $array1);

        array_walk($insertions, $deserialize);
        array_walk($deletions, $deserialize);

        return array('insertions' => $insertions, 'deletions' => $deletions);
    }

}
