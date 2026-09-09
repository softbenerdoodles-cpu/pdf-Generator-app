<?php

namespace App\Documents;

use InvalidArgumentException;

/**
 * A single input in a template's schema.
 *
 * Fields are the one source of truth for a template's shape: they are
 * serialised to the React form builder and compiled into Laravel validation
 * rules, so the browser and the server can never drift apart.
 */
class Field
{
    public const TEXT = 'text';

    public const TEXTAREA = 'textarea';

    public const NUMBER = 'number';

    public const MONEY = 'money';

    public const DATE = 'date';

    public const EMAIL = 'email';

    public const SELECT = 'select';

    public const GROUP = 'group';

    public const REPEATER = 'repeater';

    protected bool $required = false;

    protected ?string $help = null;

    protected ?string $placeholder = null;

    protected int $maxLength = 255;

    /** @var array<string, string> */
    protected array $options = [];

    /** @var list<Field> */
    protected array $fields = [];

    protected int $min = 0;

    protected ?int $max = null;

    protected mixed $default = null;

    final public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly string $label,
    ) {}

    public static function text(string $name, string $label): static
    {
        return new static(self::TEXT, $name, $label);
    }

    public static function textarea(string $name, string $label): static
    {
        return new static(self::TEXTAREA, $name, $label);
    }

    public static function number(string $name, string $label): static
    {
        return new static(self::NUMBER, $name, $label);
    }

    public static function money(string $name, string $label): static
    {
        return new static(self::MONEY, $name, $label);
    }

    public static function date(string $name, string $label): static
    {
        return new static(self::DATE, $name, $label);
    }

    public static function email(string $name, string $label): static
    {
        return new static(self::EMAIL, $name, $label);
    }

    /** @param  array<string, string>  $options */
    public static function select(string $name, string $label, array $options): static
    {
        return (new static(self::SELECT, $name, $label))->options($options);
    }

    /** A fixed set of sub-fields stored as a nested object. */
    public static function group(string $name, string $label, array $fields): static
    {
        return (new static(self::GROUP, $name, $label))->fields($fields);
    }

    /** A repeating list of rows, each row holding the given sub-fields. */
    public static function repeater(string $name, string $label, array $fields): static
    {
        return (new static(self::REPEATER, $name, $label))->fields($fields);
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function help(string $help): static
    {
        $this->help = $help;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function maxLength(int $maxLength): static
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    /** @param  array<string, string>  $options */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /** @param  list<Field>  $fields */
    public function fields(array $fields): static
    {
        foreach ($fields as $field) {
            if (! $field instanceof self) {
                throw new InvalidArgumentException('Nested fields must be Field instances.');
            }
        }

        $this->fields = array_values($fields);

        return $this;
    }

    /** Row bounds for a repeater. */
    public function rows(int $min, ?int $max = null): static
    {
        $this->min = $min;
        $this->max = $max;

        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    /** @return list<Field> */
    public function children(): array
    {
        return $this->fields;
    }

    /**
     * Compile this field — and anything nested under it — into validation
     * rules keyed by their dot path beneath $prefix.
     *
     * @return array<string, list<string>>
     */
    public function rules(string $prefix): array
    {
        $key = $prefix.$this->name;
        $presence = $this->required ? 'required' : 'nullable';

        return match ($this->type) {
            self::GROUP => $this->nestedRules($key, [$presence, 'array']),
            self::REPEATER => $this->repeaterRules($key, $presence),
            default => [$key => $this->scalarRules($presence)],
        };
    }

    /** @return list<string> */
    protected function scalarRules(string $presence): array
    {
        return match ($this->type) {
            self::NUMBER => [$presence, 'numeric'],
            self::MONEY => [$presence, 'numeric', 'min:0', 'max:999999999'],
            self::DATE => [$presence, 'date'],
            self::EMAIL => [$presence, 'email', 'max:255'],
            self::SELECT => [$presence, 'string', 'in:'.implode(',', array_keys($this->options))],
            self::TEXTAREA => [$presence, 'string', 'max:'.max($this->maxLength, 5000)],
            default => [$presence, 'string', 'max:'.$this->maxLength],
        };
    }

    /** @return array<string, list<string>> */
    protected function nestedRules(string $key, array $own): array
    {
        $rules = [$key => $own];

        foreach ($this->fields as $field) {
            $rules += $field->rules($key.'.');
        }

        return $rules;
    }

    /** @return array<string, list<string>> */
    protected function repeaterRules(string $key, string $presence): array
    {
        $own = [$presence, 'array'];

        if ($this->min > 0) {
            $own[] = 'min:'.$this->min;
        }

        if ($this->max !== null) {
            $own[] = 'max:'.$this->max;
        }

        $rules = [$key => $own];

        foreach ($this->fields as $field) {
            $rules += $field->rules($key.'.*.');
        }

        return $rules;
    }

    /**
     * Human labels for validation messages, keyed by the same dot paths the
     * rules use, so errors read "The unit price field is required" rather
     * than naming data.items.0.unit_price.
     *
     * @return array<string, string>
     */
    public function attributes(string $prefix): array
    {
        $key = $prefix.$this->name;
        $labels = [$key => mb_strtolower($this->label)];

        $childPrefix = match ($this->type) {
            self::GROUP => $key.'.',
            self::REPEATER => $key.'.*.',
            default => null,
        };

        if ($childPrefix === null) {
            return $labels;
        }

        foreach ($this->fields as $field) {
            $labels += $field->attributes($childPrefix);
        }

        return $labels;
    }

    /**
     * The value this field holds in an untouched document, so every React
     * input starts controlled rather than switching from undefined to a value.
     */
    public function blankValue(): mixed
    {
        if ($this->default !== null) {
            return $this->default;
        }

        return match ($this->type) {
            self::GROUP => $this->blankRow(),
            self::REPEATER => array_fill(0, max($this->min, 1), $this->blankRow()),
            self::SELECT => (string) (array_key_first($this->options) ?? ''),
            default => '',
        };
    }

    /** @return array<string, mixed> */
    protected function blankRow(): array
    {
        $row = [];

        foreach ($this->fields as $field) {
            $row[$field->name] = $field->blankValue();
        }

        return $row;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'name' => $this->name,
            'label' => $this->label,
            'required' => $this->required,
            'help' => $this->help,
            'placeholder' => $this->placeholder,
            'options' => $this->options ?: null,
            'fields' => $this->fields
                ? array_map(fn (Field $field) => $field->toArray(), $this->fields)
                : null,
            'min' => $this->type === self::REPEATER ? $this->min : null,
            'max' => $this->max,
            'default' => $this->default,
        ], fn ($value) => $value !== null && $value !== false);
    }
}
