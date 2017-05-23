<?php namespace Felixkiss\UniqueWithValidator;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/*
 * Changes to the original version have been borrowed from the following PR made by LittleBigDev
 *
 * https://github.com/felixkiss/uniquewith-validator/pull/66/commits/133fb38d07671ae0769fca2d5200f1f3fc81034b
*/
class Validator
{
    public function validateUniqueWith($attribute, $value, $parameters, $validator)
    {
        $ruleParser = new RuleParser($attribute, $value, $parameters, $validator->getData());

        // The presence verifier is responsible for counting rows within this
        // store mechanism which might be a relational database or any other
        // permanent data store like Redis, etc. We will use it to determine
        // uniqueness.
        $presenceVerifier = $validator->getPresenceVerifier();

        // Set the connection
        $presenceVerifier->setConnection($ruleParser->getConnection());

        return $presenceVerifier->getCount(
            $ruleParser->getTable(),
            $ruleParser->getPrimaryField(),
            $ruleParser->getPrimaryValue(),
            $ruleParser->getIgnoreValue(),
            $ruleParser->getIgnoreColumn(),
            $ruleParser->getAdditionalFields()
        ) == 0;
    }

    public function replaceUniqueWith($message, $attribute, $rule, $parameters, $translator)
    {
        $ruleParser = new RuleParser($attribute, null, $parameters);
        $fields = $ruleParser->getDataFields();

        $customAttributes = $translator->trans('validation.attributes');

        // Check if translator has custom validation attributes for the fields
        $fields = array_map(function ($field) use ($customAttributes) {
            return Arr::get($customAttributes, $field) ?: str_replace('_', ' ', Str::snake($field));
        }, $fields);

        return str_replace(':fields', implode(', ', $fields), $message);
    }
}
