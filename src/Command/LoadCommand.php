<?php

namespace Survos\LocationBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Survos\LocationBundle\Entity\Location;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Intl\Countries;

#[AsCommand(
    name: 'survos:location:load',
    description: 'Load Symfony countries, ISO 2nd level, and world cities.',
)]
class LoadCommand extends Command
{
    private EntityManagerInterface $em;
    private array $levels = ['Continent', 'Country','State','City'];

    public function __construct(ManagerRegistry $registry, string $name=null)
    {
        // since we don't know EM is associated with the Location    table, pass in the registry instead.
        parent::__construct($name);
        $this->em = $registry->getManager('survos_location');
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        $this->load($this->em);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    /**
     * @var mixed[][]
     */
    private array $lvlCache = [];

    public function load(ObjectManager $manager): void
    {
        $this->output = new ConsoleOutput();
        $this->manager = $manager;
        $this->locationRepository = $manager->getRepository(Location::class);
        $this->locationRepository->createQueryBuilder('l')->delete()->getQuery()->execute();
        $this->em->flush();

        $this->loadCountries();
        $this->loadIso3166();
        $this->loadCities();
    }


    private function loadCountries(): void
    {
        $lvl = 1;
        $this->lvlCache[$lvl] = [];
        $this->output->writeln("Loading Countries from Symfony Intl component");
        $countries = Countries::getNames();
        foreach ($countries as $alpha2=>$name) {
            $countryCode = $alpha2;
            $location = new Location($countryCode, $name, $lvl);
            $location
                ->setAlpha2($alpha2);
            $this->manager->persist($location);
            $this->lvlCache[$lvl][$location->getCode()] = $location;
        }
        $this->flushLevel($lvl);
    }

    function flushLevel(int $lvl): void
    {
        $this->output->writeln("Flushing level $lvl " . $this->levels[$lvl]);
        $this->manager->flush(); // set the IDs
        $this->manager->clear();
        //        $count = $this->locationRepository->count(['lvl'=> $lvl]);
        $count = $this->locationRepository->count([]);
        $this->output->writeln(sprintf("After level $lvl Count is: %d", $count));
        assert($count, "no $lvl locations!");

    }

    // l "states/regions/subcountries" (lvl-2), and 15000 largest cities(lvl-3).
    private function loadIso3166(): void
    {
        $lvl = 2;
        $this->lvlCache[$lvl] = [];

        $countriesByName = [];
        $json = file_get_contents('https://raw.githubusercontent.com/olahol/iso-3166-2.json/master/iso-3166-2.json');
        // $json = file_get_contents('public/iso-3166-2.json');
        $regions = [];
        $regionsByName = [];

        foreach (json_decode($json) as $countryCode => $country) {

            $parent = $this->lvlCache[$lvl-1][$countryCode] ?? false;
            if (!$parent) {
                continue; // missing TP, East Timor.
            }
            assert($parent, "Missing $countryCode, $country->name in " . implode(',', array_keys($this->lvlCache[$lvl-1])));

            foreach ($country->divisions as $stateCode => $stateName) {
                $location = (new Location($stateCode, $stateName, $lvl))
                    ->setParent($parent);
                $this->manager->persist($location);
                $this->lvlCache[$lvl][$stateName] = $location;
            }
        }
        $this->flushLevel($lvl);
    }

    public function loadCities(): void
    {
        $lvl = 3;
//        $this->lvlCache[$lvl] = [];

        // dump($regions['United States']);

        // now that we have the names loaded into the arrays, we can use them for lookups

        // https://datahub.io/core/world-cities or https://simplemaps.com/data/us-cities
//            $fn = 'https://datahub.io/core/world-cities/r/world-cities.json'; //  or https://simplemaps.com/data/us-cities';
        $fn = __DIR__ . '/../../data/world-cities.json';
//        if (!file_exists($fn)) {
//
//        }
//        assert(file_exists($fn), $fn);
        $json = file_get_contents($fn);
        $data = json_decode($json);

        foreach ($data as $idx => $cityData) {
//            $city = (new City())
//                ->setName($cityData->name)
//                ->setCode($cityData->geonameid)
//                ->setCountry($cityData->country)
//                ->setSubcountry($cityData->subcountry);
//            $this->manager->persist($city);
            // $this->output->writeln(sprintf("%d) Found %s in %s, %s ", $idx, $cityData->name, $cityData->subcountry, $cityData->country));

            // $country = $countriesByName[$data->country];
            if ($parent = $this->lvlCache[$lvl-1][$cityData->subcountry] ?? false) {
                $cityCode = $cityData->geonameid; // unique, could also be based on country / state / cityName
                $cityLoc = (new Location($cityCode, $cityData->name, $lvl))
                    ->setParent($parent)
                ;
                // set by ID?
                $this->manager->persist($cityLoc);
            } else {
                continue;
                $this->flushLevel($lvl);
                dd($cityData);
                assert($parent, $cityData->subcountry . " missing in " . implode("\n", array_keys($this->lvlCache[$lvl-1])));
                // we could create a fake subcountry, but really we need to find level 2
//                $this->output->writeln(sprintf("Unable to find subcountry %s %s in country (%s)", $cityData->subcountry, $cityData->geonameid, $cityData->country));
            }
            // $this->manager->flush();
        }
        $this->flushLevel($lvl);
    }

}
