<?php
/**
 * Instant Analytics plugin for Craft CMS
 *
 * @author    nystudio107
 * @copyright Copyright (c) 2017 nystudio107
 * @link      http://nystudio107.com
 * @package   InstantAnalytics
 * @since     1.0.0
 */

namespace nystudio107\instantanalytics\helpers;

use Craft;
use craft\base\Element;
use craft\base\Field as BaseField;
use craft\ckeditor\Field as CKEditorField;
use craft\elements\MatrixBlock;
use craft\elements\User;
use craft\fields\Assets as AssetsField;
use craft\fields\Categories as CategoriesField;
use craft\fields\Matrix as MatrixField;
use craft\fields\PlainText as PlainTextField;
use craft\fields\Tags as TagsField;
use craft\models\FieldLayout;
use craft\models\Volume;
use craft\redactor\Field as RedactorField;
use Exception;

/**
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class Field
{
    // Constants
    // =========================================================================

    public const TEXT_FIELD_CLASS_KEY = 'text';
    public const ASSET_FIELD_CLASS_KEY = 'asset';
    public const BLOCK_FIELD_CLASS_KEY = 'block';

    protected const FIELD_CLASSES = [
        self::TEXT_FIELD_CLASS_KEY => [
            CKEditorField::class,
            PlainTextField::class,
            RedactorField::class,
            TagsField::class,
            CategoriesField::class,
        ],
        self::ASSET_FIELD_CLASS_KEY => [
            AssetsField::class,
        ],
        self::BLOCK_FIELD_CLASS_KEY => [
            MatrixField::class,
        ],
    ];

    // Static Methods
    // =========================================================================

    /**
     * Return all the fields from the $layout that are of the type
     * $fieldClassKey
     *
     * @param string $fieldClassKey
     * @param FieldLayout $layout
     * @param bool $keysOnly
     *
     * @return array
     */
    public static function fieldsOfTypeFromLayout(
        string      $fieldClassKey,
        FieldLayout $layout,
        bool        $keysOnly = true
    ): array
    {
        $foundFields = [];
        if (!empty(self::FIELD_CLASSES[$fieldClassKey])) {
            $fieldClasses = self::FIELD_CLASSES[$fieldClassKey];
            $fields = $layout->getFields();
            /** @var  $field BaseField */
            foreach ($fields as $field) {
                /** @var array $fieldClasses */
                foreach ($fieldClasses as $fieldClass) {
                    if ($field instanceof $fieldClass) {
                        $foundFields[$field->handle] = $field->name;
                    }
                }
            }
        }

        // Return only the keys if asked
        if ($keysOnly) {
            $foundFields = array_keys($foundFields);
        }

        return $foundFields;
    }

    /**
     * Return all of the fields in the $element of the type $fieldClassKey
     *
     * @param Element $element
     * @param string $fieldClassKey
     * @param bool $keysOnly
     *
     * @return array
     */
    public static function fieldsOfTypeFromElement(
        Element $element,
        string  $fieldClassKey,
        bool    $keysOnly = true
    ): array
    {
        $foundFields = [];
        $layout = $element->getFieldLayout();
        if ($layout !== null) {
            $foundFields = self::fieldsOfTypeFromLayout($fieldClassKey, $layout, $keysOnly);
        }

        return $foundFields;
    }

    /**
     * Return all of the fields from Users layout of the type $fieldClassKey
     *
     * @param string $fieldClassKey
     * @param bool $keysOnly
     *
     * @return array
     */
    public static function fieldsOfTypeFromUsers(string $fieldClassKey, bool $keysOnly = true): array
    {
        $layout = Craft::$app->getFields()->getLayoutByType(User::class);

        return self::fieldsOfTypeFromLayout($fieldClassKey, $layout, $keysOnly);
    }

    /**
     * Return all the fields from all Asset Volume layouts of the type
     * $fieldClassKey
     *
     * @param string $fieldClassKey
     * @param bool $keysOnly
     *
     * @return array
     */
    public static function fieldsOfTypeFromAssetVolumes(string $fieldClassKey, bool $keysOnly = true): array
    {
        $foundFields = [];
        $volumes = Craft::$app->getVolumes()->getAllVolumes();
        foreach ($volumes as $volume) {
            /** @var Volume $volume */
            try {
                $layout = $volume->getFieldLayout();
            } catch (Exception $e) {
                $layout = null;
            }
            if ($layout) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $foundFields = array_merge(
                    $foundFields,
                    self::fieldsOfTypeFromLayout($fieldClassKey, $layout, $keysOnly)
                );
            }
        }

        return $foundFields;
    }

    /**
     * Return all the fields from all Global Set layouts of the type
     * $fieldClassKey
     *
     * @param string $fieldClassKey
     * @param bool $keysOnly
     *
     * @return array
     */
    public static function fieldsOfTypeFromGlobals(string $fieldClassKey, bool $keysOnly = true): array
    {
        $foundFields = [];
        $globals = Craft::$app->getGlobals()->getAllSets();
        foreach ($globals as $global) {
            $layout = $global->getFieldLayout();
            if ($layout) {
                $fields = self::fieldsOfTypeFromLayout($fieldClassKey, $layout, $keysOnly);
                // Prefix the keys with the global set name
                $prefix = $global->handle;
                $fields = array_combine(
                    array_map(static function ($key) use ($prefix) {
                        return $prefix . '.' . $key;
                    }, array_keys($fields)),
                    $fields
                );
                // Merge with any fields we've already found
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $foundFields = array_merge(
                    $foundFields,
                    $fields
                );
            }
        }

        return $foundFields;
    }

    /**
     * Return all the fields in the $matrixBlock of the type $fieldType class
     *
     * @param MatrixBlock $matrixBlock
     * @param string $fieldType
     * @param bool $keysOnly
     *
     * @return array
     */
    public static function matrixFieldsOfType(MatrixBlock $matrixBlock, string $fieldType, bool $keysOnly = true): array
    {
        $foundFields = [];

        try {
            $matrixBlockTypeModel = $matrixBlock->getType();
        } catch (Exception $e) {
            $matrixBlockTypeModel = null;
        }
        if ($matrixBlockTypeModel) {
            $fields = $matrixBlockTypeModel->getCustomFields();
            /** @var  $field BaseField */
            foreach ($fields as $field) {
                if ($field instanceof $fieldType) {
                    $foundFields[$field->handle] = $field->name;
                }
            }
        }

        // Return only the keys if asked
        if ($keysOnly) {
            $foundFields = array_keys($foundFields);
        }

        return $foundFields;
    }
}
