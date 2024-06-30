<?php

namespace Zymawy\Dgraph\Types;

use Zymawy\Dgraph\Contracts\TypeContract;

abstract class TypeBase implements TypeContract
{
    protected array $directives = [];

    public function __toString(): string
    {
        $indexString = $this->getDirectiveString();

        return "{$this->name}{$indexString}";
    }

    public function getDirectiveString(): string
    {
        if (empty($this->directives)) {
            return '';
        }

        $validDirectives = ['@index', '@count', '@lang', '@dgraph', '@search', '@cascade', '@upsert', '@id'];

        $directives = array_filter($this->directives, function ($directive) use ($validDirectives) {
            foreach ($validDirectives as $validDirective) {
                if (strpos($directive, $validDirective) !== false) {
                    return true;
                }
            }

            return false;
        });

        if (empty($directives)) {
            return '';
        }

        $directivesString = implode(' ', $directives);

        return " $directivesString";
    }
}
