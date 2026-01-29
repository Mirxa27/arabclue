<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'is_private',
        'is_system',
        'description'
    ];

    /**
     * Cast attributes to native types
     */
    protected $casts = [
        'is_private' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get a setting by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value
     * 
     * @param string $key
     * @param mixed $value
     * @param array $attributes Additional attributes
     * @return Setting
     */
    public static function set(string $key, $value, array $attributes = [])
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;

        foreach ($attributes as $attribute => $attributeValue) {
            if (in_array($attribute, $setting->getFillable())) {
                $setting->{$attribute} = $attributeValue;
            }
        }

        $setting->save();
        return $setting;
    }

    /**
     * Get all settings in a specific group
     * 
     * @param string $group
     * @return array
     */
    public static function getGroup(string $group)
    {
        $settings = static::where('group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = static::castValue($setting->value, $setting->type);
        }

        return $result;
    }

    /**
     * Cast value based on type
     * 
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
            case 'int':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'array':
            case 'json':
                return json_decode($value, true) ?: [];
            case 'object':
                return json_decode($value) ?: new \stdClass;
            case 'string':
            default:
                return $value;
        }
    }
}
