<?php

namespace Tests\Legacy\Other;

use App\Models\Drafting\DraftOffer;
use App\Models\Drafting\DraftPitch;
use App\Validators\DraftValidator;
use App\Validators\OfferContactValidator;
use App\Validators\OfferDocumentValidator;
use App\Validators\OfferValidator;
use Exception;
use Illuminate\Support\Arr;
use Symfony\Component\Yaml\Yaml;
use Tests\Legacy\LegacyTestCase;

final class DraftValidatorTest extends LegacyTestCase
{
    public function testDraftValidatorMatchesOfferValidators(): void
    {
        $validator = DraftValidator::UPDATE_DATA_OFFER;
        $frankensteinValidator = array_merge(
            ['offer' => null],
            Arr::dot(OfferValidator::CREATE, 'offer.'),
            ['offer_documents' => null, 'offer_documents.*' => null],
            Arr::dot(OfferDocumentValidator::CREATE, 'offer_documents.*.'),
            ['offer_contacts' => null, 'offer_contacts.*' => null],
            Arr::dot(OfferContactValidator::CREATE, 'offer_contacts.*.')
        );
        unset(
            $frankensteinValidator['offer_documents.*.offer_id'],
            $frankensteinValidator['offer_contacts.*.offer_id']
        );
        $this->assertSame(array_keys($validator), array_keys($frankensteinValidator));
    }

    public function testDraftHelperConstantsPassDraftValidator(): void
    {
        $valid = true;

        try {
            DraftValidator::validate('UPDATE_DATA_OFFER', DraftOffer::BLUEPRINT);
            DraftValidator::validate('UPDATE_DATA_PITCH', DraftPitch::BLUEPRINT);
        } catch (Exception $exception) {
            foreach ($exception->errors() as $source => $messages) {
                foreach ($messages as $message) {
                    dump(json_decode($message)[3]);
                }
            }
            $valid = false;
        }
        $this->assertTrue($valid);
    }

    public function testDraftDataDocumentationMatchesOfferDocumentation(): void
    {
        $swagger = Yaml::parseFile(__DIR__ . '/../../../resources/docs/swagger.yml');
        $draftData = $swagger['definitions']['DraftDataRead'];

        $offer = $swagger['definitions']['OfferCreate'];
        $offer['properties']['cover_photo']['type'] = 'string';
        $offer['properties']['video']['type'] = 'string';
        $this->assertSame($offer, $draftData['properties']['offer']);

        $offerContact = $swagger['definitions']['OfferContactCreate'];
        unset($offerContact['properties']['offer_id']);
        $this->assertSame($offerContact, $draftData['properties']['offer_contacts']['items']);

        $offerDocument = $swagger['definitions']['OfferDocumentCreate'];
        unset($offerDocument['properties']['offer_id']);
        $offerDocument['properties']['document']['type'] = 'string';
        $this->assertSame($offerDocument, $draftData['properties']['offer_documents']['items']);
    }

    public function testDraftDataDocumentationMatchesPitchDocumentation(): void
    {
        $swagger = Yaml::parseFile(__DIR__ . '/../../../resources/docs/swagger.yml');
        $draftData = $swagger['definitions']['DraftDataRead'];

        $pitch = $swagger['definitions']['PitchCreate'];
        $pitch['properties']['cover_photo']['type'] = 'string';
        $pitch['properties']['video']['type'] = 'string';
        $this->assertSame($pitch, $draftData['properties']['pitch']);

        $pitchContact = $swagger['definitions']['PitchContactCreate'];
        unset($pitchContact['properties']['pitch_id']);
        $this->assertSame($pitchContact, $draftData['properties']['pitch_contacts']['items']);

        $pitchDocument = $swagger['definitions']['PitchDocumentCreate'];
        unset($pitchDocument['properties']['pitch_id']);
        $pitchDocument['properties']['document']['type'] = 'string';
        $this->assertSame($pitchDocument, $draftData['properties']['pitch_documents']['items']);

        $carbonCertification = $swagger['definitions']['CarbonCertificationCreate'];
        unset($carbonCertification['properties']['pitch_id']);
        $this->assertSame($carbonCertification, $draftData['properties']['carbon_certifications']['items']);

        $restorationMethodMetric = $swagger['definitions']['RestorationMethodMetricCreate'];
        unset($restorationMethodMetric['properties']['pitch_id']);
        $this->assertSame($restorationMethodMetric, $draftData['properties']['restoration_method_metrics']['items']);

        $treeSpecies = $swagger['definitions']['TreeSpeciesCreate'];
        unset($treeSpecies['properties']['pitch_id']);
        $this->assertSame($treeSpecies, $draftData['properties']['tree_species']['items']);
    }
}
