<?php

/**
 * Class Validator
 *
 * This class provides a set of validation rules for validating input data.
 */
class Validator
{
    /** @var array Stores validation errors */
    protected array $errors = [];

    /** @var array Input data to be validated */
    protected array $data;

    /** @var array Validation rules */
    protected array $rules;

    /** @var string Default language for validation messages */
    protected static string $lang = 'pt';

    /**
     * Validation messages for different languages.
     *
     * @var array
     */
    protected static array $messages = [
        'pt' => [
            'required' => "O campo :field é obrigatório.",
            'email' => "O campo :field deve ser um email válido.",
            'min' => "O campo :field deve ter pelo menos :param caracteres.",
            'max' => "O campo :field deve ter no máximo :param caracteres.",
            'unique' => "O campo :field já está em uso.",
            'equals' => "O campo :field deve ser igual a :param.",
            'name' => "O campo :field deve conter apenas letras e espaços.",
            'username' => "O campo :field deve conter apenas letras ascii e undersocres.",
            'checked' => "O campo :field deve ser marcado."
        ],
        'en' => [
            'required' => "The field :field is required.",
            'email' => "The field :field must be a valid email.",
            'min' => "The field :field must be at least :param characters.",
            'max' => "The field :field must be at most :param characters.",
            'unique' => "The field :field is already in use.",
            'equals' => "The field :field must be equal to :param.",
            'name' => "The field :field must contain only letters and spaces.",
            'username' => "The field :field must contain only acci characters and underscores.",
            'checked' => "The field :field must be checked."
        ]
    ];

    /**
     * Validator constructor.
     *
     * @param array $data Input data
     * @param array $rules Validation rules
     */
    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    /**
     * Sets the language for validation messages.
     *
     * @param string $lang Language code ('pt' or 'en')
     */
    public static function setLanguage(string $lang)
    {
        self::$lang = in_array($lang, ['pt', 'en']) ? $lang : 'pt';
    }

    /**
     * Validates the input data based on the defined rules.
     */
    protected function validate()
    {
        foreach ($this->rules as $field => $ruleSet) {

            if (is_string($ruleSet)) {
                $ruleSet = explode("|", $ruleSet);
            }

            foreach ($ruleSet as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramStr] = explode(':', $rule);
                    $params = explode(',', $paramStr);
                }

                $method = 'validate' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    $this->$method($field, $this->data[$field] ?? null, ...$params);
                }
            }
        }
    }

    public static function check(int|string|bool|null|float $data, $rules)
    {
        return (new self(['field' => $data], $rules))->errors();
    }

    /**
     * Checks if the validation has failed.
     *
     * @return bool True if validation failed, false otherwise
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Checks if the validation is still valid (i.e., no errors).
     *
     * @return bool True if still valid, false otherwise
     */
    public function isStillValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Retrieves validation errors.
     *
     * @return array List of errors
     */
    public function errors()
    {
        return $this->errors;
    }

    /*
     * Retrieves All validation errors.
     *
     * @return array List of errors
     */
    public function firstError(): array
    {
        return array_values($this->errors())[0];
    }

    /**
     * Retrieves validated data.
     *
     * @return array Validated data
     */
    public function validated(): array
    {
        return $this->fails() ? [] : $this->data;
    }

    /**
     * Adds an error message to the errors array.
     *
     * @param string $field Field name
     * @param string $rule Validation rule
     * @param mixed ...$params Additional parameters
     */
    protected function addError(string $field, string $rule, ...$params)
    {
        if (!isset($this->errors[$field])) {
            $message = self::$messages[self::$lang][$rule] ?? "Erro de validação no campo $field.";
            $message = str_replace(':field', $field, $message);
            foreach ($params as $param) {
                $message = str_replace(':param', $param, $message);
            }
            $this->errors[$field] = $message;
        }
    }

    /** Validation methods */
    protected function validateRequired(string $field, $value)
    {
        if (empty($value)) $this->addError($field, 'required');
    }
    protected function validateEmail(string $field, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) $this->addError($field, 'email');
    }
    protected function validateMin(string $field, $value, int $min)
    {
        if (strlen($value) < $min) $this->addError($field, 'min', $min);
    }
    protected function validateMax(string $field, $value, int $max)
    {
        if (strlen($value) > $max) $this->addError($field, 'max', $max);
    }
    protected function validateEquals(string $field, $value, $compareValue)
    {
        if ($value !== $compareValue) $this->addError($field, 'equals', $compareValue);
    }
    protected function validateName(string $field, $value)
    {
        if (!preg_match("/^[a-zA-ZÀ-ÿ' -]+$/", $value)) $this->addError($field, 'name');
    }
    protected function validateusername(string $field, $value)
    {
        if (!preg_match("/^[a-zA-Z0-9-_]+$/", $value)) $this->addError($field, 'name');
    }
    protected function validateChecked(string $field, $value)
    {
        if (empty($value) || $value !== 'on') $this->addError($field, 'checked');
    }
    protected function validateUnique(string $field, $value, string $table, ?string $column = null)
    {
        $column = $column ?: $field;
        $existing = DB::getInstance()->query("SELECT COUNT(*) FROM $table WHERE $column = ?", [$value])[0];
        $count = $existing['COUNT(*)'];
        if ($count > 0) $this->addError($field, 'unique');
    }
}
