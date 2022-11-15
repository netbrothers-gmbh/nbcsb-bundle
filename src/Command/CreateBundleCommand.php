<?php

/**
 * NetBrothersCreateBundle
 *
 * @author Stefan Wessel, NetBrothers GmbH
 * @date 16.12.20
 *
 */

namespace NetBrothers\NbCsbBundle\Command;

use NetBrothers\NbCsbBundle\Services\CreateBundleService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/** Creating skeleton for symfony bundle
 *
 * Class CreateBundleCommand
 * @package NetBrothers\NbCreateSymfonyBundle\Command
 */
#[AsCommand(
    name: 'netbrothers:make-bundle',
    description: 'Creates symfony bundle under `src`',
)]
class CreateBundleCommand  extends Command
{

    /** @var string[] templates to manipulate */
    private array $templateFiles = [];

    /** @var string[] template files in templateDir */
    private array $templates = [
        'bundle' => 'Bundle.txt',
        'extension' => 'Extension.txt',
        'services' => 'services.xml',
        'routes'  => 'routes.xml'
    ];

    /** @var array benötigte Haupt-Schlüssel in der Konfiguration */
    private array $configKeys = ['template_dir'];

    /** @var array */
    private array $config = [];

    protected function configure(): void
    {
        $this->addArgument('bundleName', InputArgument::REQUIRED, 'name of bundle');
    }

    /**
     * CreateBundleCommand constructor.
     * @param array $config Configuration
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
        $this->setTemplates();
        parent::__construct();
    }

    /** setting config
     *
     * @param array $config
     * @throws \Exception
     */
    private function setConfig(array $config = []): void
    {
        foreach ($this->configKeys as $key) {
            if (!array_key_exists($key, $config)) {
                $message = "Config $key missing! Do you defined it in config?";
                throw new \Exception($message);
            }
            $value = $config[$key];
            if (empty($value)) {
                $message = "Value missing for $key! Do you defined it in config?";
                throw new \Exception($message);
            }
            $this->config[$key] = $value;
        }
    }

    /** checking templates
     *
     * @throws \Exception
     */
    private function setTemplates(): void
    {
        if ($this->config['template_dir'] == 'default') {
            $templateDir = __DIR__ . '/../../installation/templates/';
        } else {
            $templateDir = $this->config['template_dir'];
        }
        $templateDir = (substr($templateDir, -1, 1) == '/') ? $templateDir : $templateDir . '/';
        if (!is_dir($templateDir) || !is_readable($templateDir)) {
            throw new \Exception('Cannot access template directory ' . $templateDir);
        }
        foreach ($this->templates as $key => $fileName) {
            $template = $templateDir .  $fileName;
            if (!is_file($template) || !is_readable($template)) {
                throw new \Exception('Cannot access template file ' . $template);
            }
            $this->templateFiles[$key] = $template;
        }
    }

    /**
     * @param string $bundleName
     * @return bool
     */
    private function checkBundleName(string $bundleName): bool
    {
        if (preg_match('/^([A-Z][a-z]*([A-Z][a-z]*)*Bundle)$/', $bundleName)) {
            return true;
        }
        return false;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $bundleName = $input->getArgument('bundleName');

        if (true !== $this->checkBundleName($bundleName)) {
            $io->error('Wrong name - pattern does not match ^([A-Z][a-z]*([A-Z][a-z]*)*Bundle)$');
            return Command::INVALID;
        }

        $pathToWrite = getcwd() . '/src/';
        $workingDir = $pathToWrite . $bundleName;
        $srcPerms = fileperms($pathToWrite) & 0777;

        $io->note("Creating bundle `$bundleName` in " . $workingDir);

        $service = new CreateBundleService(
            $bundleName,
            $workingDir,
            $srcPerms,
            $this->templateFiles
        );

        if (true !== $service->createBundleDirectories()) {
            $io->error($service->getErrMsg());
            return Command::FAILURE;
        }
        if (true !== $service->copyRessourceFiles()) {
            $io->error($service->getErrMsg());
            return Command::FAILURE;
        }

        $service->createBundleClasses();

        if (true !== $service->activateBundle()) {
            $io->newLine(2);
            $io->error($service->getErrMsg());
            $io->newLine(2);
            $errMsg = 'Bundle created with errors on activation in bundle.php!';
            $errMsg .= PHP_EOL;
            $errMsg .= 'Check Error and repair your system. You may have to ';
            $errMsg .= 'activate the bundle in bundles.php by yourself.';
            $io->caution($errMsg);
            return Command::FAILURE;
        }

        $io->success('Bundle created');
        return Command::SUCCESS;
    }
}
