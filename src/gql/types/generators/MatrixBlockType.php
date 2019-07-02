<?php
namespace craft\gql\types\generators;

use Craft;
use craft\base\Field;
use craft\elements\MatrixBlock as MatrixBlockElement;
use craft\fields\Matrix;
use craft\gql\interfaces\elements\MatrixBlock as MatrixBlockInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\types\MatrixBlock;
use craft\models\MatrixBlockType as MatrixBlockTypeModel;

/**
 * Class MatrixBlockTypeGenerator
 */
class MatrixBlockType implements BaseGenerator
{
    /**
     * @inheritdoc
     */
    public static function generateTypes($context = null): array
    {
        // If we need matrix block types for a specific Matrix field, fetch those.
        if ($context) {
            /** @var Matrix $context */
            $matrixBlockTypes = $context->getBlockTypes();
        } else {
            $matrixBlockTypes = Craft::$app->getMatrix()->getAllBlockTypes();
        }

        $gqlTypes = [];

        foreach ($matrixBlockTypes as $matrixBlockType) {
            /** @var MatrixBlockTypeModel $matrixBlockType */
            $typeName = MatrixBlockElement::getGqlTypeNameByContext($matrixBlockType);

            if (!($entity = GqlEntityRegistry::getEntity($typeName))) {
                $contentFields = $matrixBlockType->getFields();
                $contentFieldGqlTypes = [];

                /** @var Field $contentField */
                foreach ($contentFields as $contentField) {
                    $contentFieldGqlTypes[$contentField->handle] = $contentField->getContentGqlType();
                }

                $blockTypeFields = array_merge(MatrixBlockInterface::getFields(), $contentFieldGqlTypes);

                // Generate a type for each entry type
                $entity = GqlEntityRegistry::createEntity($typeName, new MatrixBlock([
                    'name' => $typeName,
                    'fields' => function() use ($blockTypeFields) {
                        return $blockTypeFields;
                    }
                ]));
            }

            $gqlTypes[$typeName] = $entity;
        }

        return $gqlTypes;
    }
}
