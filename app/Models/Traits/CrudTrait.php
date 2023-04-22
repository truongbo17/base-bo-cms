<?php

namespace Bo\Base\Models\Traits;

trait CrudTrait
{
    use HasIdentifiableAttribute;
    use HasEnumFields;
    use HasRelationshipFields;
    use HasUploadFields;
    use HasFakeFields;
    use HasTranslatableFields;

    public static function hasCrudTrait()
    {
        return true;
    }
}
