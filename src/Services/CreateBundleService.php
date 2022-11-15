<?php

/**
 * NetBrothersCreateBundle
 *
 * @author Stefan Wessel, NetBrothers GmbH
 * @date 17.12.20
 *
 */

namespace NetBrothers\NbCsbBundle\Services;

/**
 * Class CreateBundleService
 * @package NetBrothers\NbCreateSymfonyBundleBundle\Services
 */
class CreateBundleService
{
    const RESSOURCE_DIR = '/Resources/config';
    const DEPENDENCY_DIR = '/DependencyInjection';

    private array $otherDirs = ['/Controller', '/Services', '/Tests'];
    private string $resourcesDir = '/Resources/config';
    private string $dependencyDir = '/DependencyInjection';
    private int $dirMode;

    private string $workingDir;
    private string $errMsg;
    private string $bundleName;
    private array $templateFiles;

    public function getErrMsg(): string
    {
        return $this->errMsg;
    }

    /**
     * @see https://php.net/mkdir for file modes
     * @param string $bundleName name of the new bundle (PascalCase)
     * @param string $workingDir the directory the bundle resides in
     * @param int $dirMode the file mode of the to be created directories
     * @param array $templateFiles an array of template file paths
     * @return void 
     */
    public function __construct(
        string $bundleName,
        string $workingDir,
        int $dirMode = 0755,
        array $templateFiles = []
    )
    {
        $this->bundleName = $bundleName;
        $this->workingDir = $workingDir;
        $this->dirMode = $dirMode;
        $this->templateFiles = $templateFiles;
    }

    /**
     * @return bool
     */
    public function createBundleDirectories(): bool
    {
        if (true !== $this->createDir($this->workingDir)) {
            return false;
        }
        $resDir = $this->workingDir . self::RESSOURCE_DIR;
        if (true !== $this->createDir($resDir)) {
            return false;
        }
        $this->resourcesDir = $resDir;
        $depDir = $this->workingDir . self::DEPENDENCY_DIR;
        if (true !== $this->createDir($depDir)) {
            return false;
        }
        $this->dependencyDir = $depDir;
        foreach ($this->otherDirs as $dir) {
            $oDir = $this->workingDir . $dir;
            if (true !== $this->createDir($oDir)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function copyRessourceFiles(): bool
    {
        foreach ($this->templateFiles as $key => $template) {
            if ($key == 'services' || $key == 'routes') {
                $dest = $this->resourcesDir . "/$key.xml";
                if (true !== copy($template, $dest)) {
                    $this->errMsg = 'Cannot copy ' . $key . '.xml to  ' . $this->resourcesDir;
                    return false;
                }
            }
        }
        return true;
    }

    /** creating Bundle.php and Extension.php
     *
     */
    public function createBundleClasses(): void
    {
        $datum = date('d.m.Y');
        $shortName = str_replace('Bundle', '', $this->bundleName);
        $bundleSmallShortName = strtolower($shortName);
        foreach ($this->templateFiles as $key => $template) {
            if ($key == 'bundle' || $key == 'extension') {
                $content = file_get_contents($template);
                $content = preg_replace('/\{#bundleName}/', $this->bundleName, $content);
                $content = preg_replace('/\{#bundleShortName}/', $shortName, $content);
                $content = preg_replace('/\{#bundleSmallShortName}/', $bundleSmallShortName, $content);
                $content = preg_replace('/\{#datum}/', $datum, $content);
                $fileName = ($key == 'bundle') ? $this->bundleName . '.php' : $shortName . 'Extension.php';
                $dest = ($key == 'bundle') ? $this->workingDir . "/$fileName" : $this->dependencyDir . "/$fileName";
                file_put_contents($dest, $content);
            }
        }
    }

    /**
     * @return bool
     */
    public function activateBundle(): bool
    {
        $className = 'App\\' . $this->bundleName . '\\' . $this->bundleName;
        $bundlePhp = getcwd() . '/config/bundles.php';
        $backUp = getcwd() . '/config/bundles.backup';
        if (!is_dir(getcwd() . '/config')) {
            $this->errMsg = 'Cannot find directory ' . getcwd() . '/config';
            return false;
        } else if (!is_writable(getcwd() . '/config')) {
            $this->errMsg = 'Cannot write in directory ' . getcwd() . '/config';
            return false;
        } else if (!is_file($bundlePhp)) {
            $this->errMsg = 'Cannot find file ' . $bundlePhp;
            return false;
        } else if (!is_readable($bundlePhp)) {
            $this->errMsg = 'Cannot read file ' . $bundlePhp;
            return false;
        } else if (!is_writable($bundlePhp)) {
            $this->errMsg = 'Cannot write in file ' . $bundlePhp;
            return false;
        }
        $contentArray = file($bundlePhp);
        if (!rename($bundlePhp, $backUp)) {
            $this->errMsg = 'Cannot create backup ' . $backUp;
            return false;
        }
        if (!touch($bundlePhp)) {
            $this->errMsg = 'Cannot create new file ' . $bundlePhp;
            return false;
        }
        $fp = fopen($bundlePhp, 'w');
        foreach ($contentArray as $line) {
            if (preg_match('/(];)/', $line)) {
                $newLine = "\t$className::class => ['all' => true],\n";
                fwrite($fp, $newLine);
            }
            fwrite($fp, $line);
        }
        fclose($fp);
        return true;
    }

    /** creating directories
     * @param string $dir
     * @return bool
     */
    private function createDir(string $dir): bool
    {
        if (is_dir($dir)) {
            $this->errMsg = 'Cannot create existing directory ' . $dir;
            return false;
        }
        if (true !== mkdir($dir, $this->dirMode, true)) {
            $this->errMsg = 'Cannot create directory ' . $dir;
            return false;
        }
        return true;
    }
}
