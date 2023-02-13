<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Action;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\PropertyProperty;


final class NodeExtractor
{
//    public function __get(string $name)
//    {
//        // TODO: Implement __get() method.
//    }

    public static function extract(mixed $node): mixed
    {
        if( is_iterable($node)) {
            $result = [];
            foreach ($node as $currentNode){
                $nodeKey = self::getName($currentNode);
                $nodeValue = self::getValue($currentNode);

                if( $nodeKey === null){
                    $result[] = $nodeValue;
                    continue;
                }

                $result[$nodeKey] = $nodeValue;

//                $result[] = self::extract($currentNode);
            }
            return $result;
        }
        return dump(match (true) {
//            $node instanceof ArrayItem => self::getValue($node->value),
            default => dd(['Unknown node->'.__FUNCTION__ => $node])
        });
    }
    public static function getNameAndValue(mixed $node): mixed
    {
        $keys = self::getName($node);
        $default = self::getDefault($node);
        $values = self::getValue($node);

        $result = [];
        foreach ($values as $index => $value)
        {
            if (is_int($index)) {
                $result[] = $value;
                continue;
            }
            $result[$index] = $value;
        }
//        return $result;
        dd($node,$keys,$values, $result, $default,self::extract($node),);
        return match (true) {
//            $node instanceof ClassConst => self::getName($node->consts[0]->value),
//            $node instanceof ArrayItem => self::getValue($node->value),
//            $node instanceof ConstFetch => self::getName($node->name),
//            $node instanceof String_ => self::getValue($node->value),
//            $node instanceof Array_ => self::getNameAndValue($node->items),
//            $node instanceof ClassConstFetch => self::getName($node->class).'::'.self::getName($node->name),
            is_string($node) => $node,
//            is_array($node) => array_map([self::class,'getValue'],$node),
            default => dd(['Unknown getNameAndValue node' => $node])
        };
    }
    public static function getValue(mixed $node): mixed
    {
        /** @noinspection ForgottenDebugOutputInspection */
        return match (true) {
            $node instanceof ClassConst => self::getName($node->consts[0]->value),
            $node instanceof ArrayItem => self::getValue($node->value),
            $node instanceof ConstFetch => self::getName($node->name),
            $node instanceof String_ => self::getValue($node->value),
            $node instanceof Array_ => self::getNameAndValue($node->items),
            $node instanceof ClassConstFetch => self::getName($node->class).'::'.self::getName($node->name),
            is_string($node) => $node,
            is_array($node) => array_map([self::class,'getValue'],$node),
            default => dd(['Unknown value node' => $node])
        };
    }

    public static function getName(mixed $node): mixed
    {
        /** @noinspection ForgottenDebugOutputInspection */
        return match (true) {
            $node === null => null,
            is_string($node) => $node,
            is_array($node) => array_map(
                static fn(ArrayItem $item): ?string => self::getName($item),
                $node
            ),
            $node instanceof String_ => self::getName($node->value),
            $node instanceof ClassConst => self::getName($node->consts[0]->name),
            $node instanceof ClassMethod => self::getName($node->name),
            $node instanceof Class_ => self::getName($node->name),
            $node instanceof Param => self::getName($node->var),
            $node instanceof ConstFetch => $node->name->toString(),
            $node instanceof Identifier => $node->toString(),
            $node instanceof Name => $node->toString(),
            $node instanceof PropertyProperty => self::getName($node->name),
            $node instanceof Variable => self::getName($node->name),
            $node instanceof ArrayItem => self::getName($node->key),
            default => dd(['Unknown name node' => $node])
        };
    }

    public static function getParams(mixed $node): array
    {
        /** @noinspection ForgottenDebugOutputInspection */
        return match (true) {
            default => dd(['Unknown getMethodParams node' => $node]),
            $node instanceof ClassMethod => array_map(
                static fn(Param $param): string => self::getName($param),
                $node->params
            ),
        };
    }

    public static function getDefault(mixed $node): mixed
    {

//        $subjectValue = $propertyProperty->default;
//        $propertyValue = match (true) {
//            $subjectValue instanceof Array_ => array_map(
//                static function (mixed $property): mixed {
//                    $key = $property->key ?? null;
//                    $key = match (true) {
//                        $key instanceof String_ => $key->value,
//                        $key === null => null,
//                        default => dd(['temp-key' => $key])
//                    };
//
//                    $value = NodeExtractor::getValue($property);
//
//                    return match (true) {
//                        $key === null => $value,
//                        is_string($key) => [$key => $value],
//                        default => dd(['temp-property' => [$key, $value, $property]])
//                    };
//                },
//                $subjectValue->items
//            ),
//            $subjectValue instanceof ConstFetch => NodeExtractor::getName($subjectValue),
////                                $subjectValue instanceof ConstFetch => $subjectValue->name->toString(),
//            default => dd($subjectName,$subjectValue, $path)
//        };
        /** @noinspection ForgottenDebugOutputInspection */
        return match (true) {
            $node === null => null,
            is_array($node) => self::extract($node),
//            is_string($node) => $node,
////            $node instanceof ClassConst => self::getName($node->consts[0]->name),
            $node instanceof PropertyProperty => self::getValue($node->default),
//            $node instanceof ClassMethod => array_map(static fn(Param $param)=>
//
//            dd($param,NodeExtractor::getName($param))
////                foreach ($methodParams as $methodParam){
////                    $methodParamVar = $methodParam->var;
////                    $params[] = match (true) {
////                        $methodParamVar instanceof Variable => NodeExtractor::getName($methodParamVar),
////                        default => throw new RuntimeException('Should not happen!')
////                    };
////                }
//                , $node->params),
//            $node instanceof Class_ => self::getName($node->name),
//            $node instanceof ConstFetch => $node->name->toString(),
//            $node instanceof Identifier => $node->toString(),
//            $node instanceof Name => $node->toString(),
//            $node instanceof PropertyProperty => self::getName($node->name),
//            $node instanceof Variable => self::getName($node->name),
            default => dd(['Unknown getDefault node' => $node])
        };
    }
}
