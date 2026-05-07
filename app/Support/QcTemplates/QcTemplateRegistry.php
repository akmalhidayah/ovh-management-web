<?php

namespace App\Support\QcTemplates;

use App\Support\QcTemplates\Presets\BagFilterBagClothTemplate;
use App\Support\QcTemplates\Presets\BeltConveyorTemplate;
use App\Support\QcTemplates\Presets\BlowerRbsTemplate;
use App\Support\QcTemplates\Presets\CoalFeederRotorTemplate;
use App\Support\QcTemplates\Presets\CoalMillSplitSealHubRollerTemplate;
use App\Support\QcTemplates\Presets\CrusherRotorHammerSetTemplate;
use App\Support\QcTemplates\Presets\CrusherRotorSegmentTeethTemplate;
use App\Support\QcTemplates\Presets\EspAirLoadTest28Template;
use App\Support\QcTemplates\Presets\EspAirLoadTest29Template;
use App\Support\QcTemplates\Presets\EspGrateCoolerDindingTemplate;
use App\Support\QcTemplates\Presets\EspTeganganTembusInnerPartRawMillTemplate;
use App\Support\QcTemplates\Presets\FirebrickTemplate;
use App\Support\QcTemplates\Presets\GasAnalyzerInletTemplate;
use App\Support\QcTemplates\Presets\GasAnalyzerTopTemplate;
use App\Support\QcTemplates\Presets\GrateCoolerInstrumentFieldCrossbarTemplate;
use App\Support\QcTemplates\Presets\InnerPartEp02Template;
use App\Support\QcTemplates\Presets\KilnFeedInstrumentFieldTemplate;
use App\Support\QcTemplates\Presets\LimestoneCrusherMagneticSeparatorTemplate;
use App\Support\QcTemplates\Presets\MbfCoalMillDindingTemplate;
use App\Support\QcTemplates\Presets\MmcCoolerDrivePlateTemplate;
use App\Support\QcTemplates\Presets\RawMillBearingHubRollerNuBearingSupportTemplate;
use App\Support\QcTemplates\Presets\RawMillMaagGearInnerPartGearBox15Template;
use App\Support\QcTemplates\Presets\RawMillMaagGearInnerPartGearBox16Template;
use App\Support\QcTemplates\Presets\RollerAssemblyRoller1CenterPieceTemplate;
use App\Support\QcTemplates\Presets\RollerAssemblyTensionPullRodTemplate;
use App\Support\QcTemplates\Presets\RotaryKilnCastableCastingBurnerGunTemplate;
use App\Support\QcTemplates\Presets\RotaryKilnCastableOverhaulTemplate;
use App\Support\QcTemplates\Presets\SeparatorAtoxGuideVaneTemplate;
use App\Support\QcTemplates\Presets\WearSegmentRollerHubRollerTemplate;
use App\Support\QcTemplates\Presets\WearSegmentRollerTorsiBautTemplate;
use App\Support\QcTemplates\Presets\WearingTyreKilnTemplate;

class QcTemplateRegistry
{
    /**
     * @return array<int, class-string>
     */
    public static function presetClasses(): array
    {
        return [
            CrusherRotorHammerSetTemplate::class,
            BeltConveyorTemplate::class,
            CrusherRotorSegmentTeethTemplate::class,
            MmcCoolerDrivePlateTemplate::class,
            CoalMillSplitSealHubRollerTemplate::class,
            WearingTyreKilnTemplate::class,
            SeparatorAtoxGuideVaneTemplate::class,
            BlowerRbsTemplate::class,
            FirebrickTemplate::class,
            RotaryKilnCastableCastingBurnerGunTemplate::class,
            RotaryKilnCastableOverhaulTemplate::class,
            RawMillMaagGearInnerPartGearBox15Template::class,
            RawMillMaagGearInnerPartGearBox16Template::class,
            RawMillBearingHubRollerNuBearingSupportTemplate::class,
            RollerAssemblyRoller1CenterPieceTemplate::class,
            RollerAssemblyTensionPullRodTemplate::class,
            WearSegmentRollerHubRollerTemplate::class,
            WearSegmentRollerTorsiBautTemplate::class,
            LimestoneCrusherMagneticSeparatorTemplate::class,
            GrateCoolerInstrumentFieldCrossbarTemplate::class,
            KilnFeedInstrumentFieldTemplate::class,
            CoalFeederRotorTemplate::class,
            GasAnalyzerInletTemplate::class,
            GasAnalyzerTopTemplate::class,
            EspAirLoadTest28Template::class,
            EspAirLoadTest29Template::class,
            EspTeganganTembusInnerPartRawMillTemplate::class,
            InnerPartEp02Template::class,
            BagFilterBagClothTemplate::class,
            MbfCoalMillDindingTemplate::class,
            EspGrateCoolerDindingTemplate::class,
        ];
    }

    public static function all(): array
    {
        return array_map(
            fn (string $presetClass) => $presetClass::data(),
            self::presetClasses()
        );
    }

    public static function codes(): array
    {
        return array_column(self::all(), 'code');
    }

    public static function numbers(): array
    {
        return array_column(self::all(), 'number');
    }
}
