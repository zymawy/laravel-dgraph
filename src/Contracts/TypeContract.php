<?php
namespace Zymawy\Dgraph\Contracts;

interface TypeContract
{
    public function __toString(): string;
    public function getDirectiveString(): string;
}