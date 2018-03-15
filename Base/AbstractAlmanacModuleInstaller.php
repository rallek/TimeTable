<?php
/**
 * Almanac.
 *
 * @copyright Ralf Koester (RK)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Ralf Koester <ralf@familie-koester.de>.
 * @link http://k62.de
 * @link http://zikula.org
 * @version Generated by ModuleStudio 1.3.1 (https://modulestudio.de).
 */

namespace RK\AlmanacModule\Base;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Zikula\Core\AbstractExtensionInstaller;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;

/**
 * Installer base class.
 */
abstract class AbstractAlmanacModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * Install the RKAlmanacModule application.
     *
     * @return boolean True on success, or false
     *
     * @throws RuntimeException Thrown if database tables can not be created or another error occurs
     */
    public function install()
    {
        $logger = $this->container->get('logger');
        $userName = $this->container->get('zikula_users_module.current_user')->get('uname');
    
        // Check if upload directories exist and if needed create them
        try {
            $container = $this->container;
            $uploadHelper = new \RK\AlmanacModule\Helper\UploadHelper(
                $container->get('translator.default'),
                $container->get('filesystem'),
                $container->get('session'),
                $container->get('logger'),
                $container->get('zikula_users_module.current_user'),
                $container->get('zikula_extensions_module.api.variable'),
                $container->getParameter('datadir')
            );
            $uploadHelper->checkAndCreateAllUploadFolders();
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            $logger->error('{app}: User {user} could not create upload folders during installation. Error details: {errorMessage}.', ['app' => 'RKAlmanacModule', 'user' => $userName, 'errorMessage' => $exception->getMessage()]);
        
            return false;
        }
        // create all tables from according entity definitions
        try {
            $this->schemaTool->create($this->listEntityClasses());
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->__('Doctrine Exception') . ': ' . $exception->getMessage());
            $logger->error('{app}: Could not create the database tables during installation. Error details: {errorMessage}.', ['app' => 'RKAlmanacModule', 'errorMessage' => $exception->getMessage()]);
    
            return false;
        }
    
        // set up all our vars with initial values
        $this->setVar('moderationGroupForDates', 2);
        $this->setVar('dateEntriesPerPage', 10);
        $this->setVar('linkOwnDatesOnAccountPage', true);
        $this->setVar('enableShrinkingForDateDateImage', false);
        $this->setVar('shrinkWidthDateDateImage', 800);
        $this->setVar('shrinkHeightDateDateImage', 600);
        $this->setVar('thumbnailModeDateDateImage', 'inset');
        $this->setVar('thumbnailWidthDateDateImageView', 32);
        $this->setVar('thumbnailHeightDateDateImageView', 24);
        $this->setVar('thumbnailWidthDateDateImageDisplay', 240);
        $this->setVar('thumbnailHeightDateDateImageDisplay', 180);
        $this->setVar('thumbnailWidthDateDateImageEdit', 240);
        $this->setVar('thumbnailHeightDateDateImageEdit', 180);
        $this->setVar('enabledFinderTypes', 'date');
    
        $categoryRegistryIdsPerEntity = [];
    
        // add default entry for category registry (property named Main)
        $categoryHelper = new \RK\AlmanacModule\Helper\CategoryHelper(
            $this->container->get('translator.default'),
            $this->container->get('request_stack'),
            $logger,
            $this->container->get('zikula_users_module.current_user'),
            $this->container->get('zikula_categories_module.category_registry_repository'),
            $this->container->get('zikula_categories_module.api.category_permission')
        );
        $categoryGlobal = $this->container->get('zikula_categories_module.category_repository')->findOneBy(['name' => 'Global']);
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
    
        $registry = new CategoryRegistryEntity();
        $registry->setModname('RKAlmanacModule');
        $registry->setEntityname('DateEntity');
        $registry->setProperty($categoryHelper->getPrimaryProperty('Date'));
        $registry->setCategory($categoryGlobal);
    
        try {
            $entityManager->persist($registry);
            $entityManager->flush();
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->__f('Error! Could not create a category registry for the %entity% entity.', ['%entity%' => 'date']));
            $logger->error('{app}: User {user} could not create a category registry for {entities} during installation. Error details: {errorMessage}.', ['app' => 'RKAlmanacModule', 'user' => $userName, 'entities' => 'dates', 'errorMessage' => $exception->getMessage()]);
        }
        $categoryRegistryIdsPerEntity['date'] = $registry->getId();
    
        // initialisation successful
        return true;
    }
    
    /**
     * Upgrade the RKAlmanacModule application from an older version.
     *
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param integer $oldVersion Version to upgrade from
     *
     * @return boolean True on success, false otherwise
     *
     * @throws RuntimeException Thrown if database tables can not be updated
     */
    public function upgrade($oldVersion)
    {
    /*
        $logger = $this->container->get('logger');
    
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.0.0':
                // do something
                // ...
                // update the database schema
                try {
                    $this->schemaTool->update($this->listEntityClasses());
                } catch (\Exception $exception) {
                    $this->addFlash('error', $this->__('Doctrine Exception') . ': ' . $exception->getMessage());
                    $logger->error('{app}: Could not update the database tables during the upgrade. Error details: {errorMessage}.', ['app' => 'RKAlmanacModule', 'errorMessage' => $exception->getMessage()]);
    
                    return false;
                }
        }
    
        // Note there are several helpers available for making migrating your extension from Zikula 1.3 to 1.4 easier.
        // The following convenience methods are each responsible for a single aspect of upgrading to Zikula 1.4.x.
    
        // here is a possible usage example
        // of course 1.2.3 should match the number you used for the last stable 1.3.x module version.
        /* if ($oldVersion = '1.2.3') {
            // rename module for all modvars
            $this->updateModVarsTo14();
            
            // update extension information about this app
            $this->updateExtensionInfoFor14();
            
            // rename existing permission rules
            $this->renamePermissionsFor14();
            
            // rename existing category registries
            $this->renameCategoryRegistriesFor14();
            
            // rename all tables
            $this->renameTablesFor14();
            
            // remove event handler definitions from database
            $this->dropEventHandlersFromDatabase();
            
            // update module name in the hook tables
            $this->updateHookNamesFor14();
            
            // update module name in the workflows table
            $this->updateWorkflowsFor14();
        } * /
    
        // remove obsolete persisted hooks from the database
        //$this->hookApi->uninstallSubscriberHooks($this->bundle->getMetaData());
    */
    
        // update successful
        return true;
    }
    
    /**
     * Renames the module name for variables in the module_vars table.
     */
    protected function updateModVarsTo14()
    {
        $conn = $this->getConnection();
        $conn->update('module_vars', ['modname' => 'RKAlmanacModule'], ['modname' => 'Almanac']);
    }
    
    /**
     * Renames this application in the core's extensions table.
     */
    protected function updateExtensionInfoFor14()
    {
        $conn = $this->getConnection();
        $conn->update('modules', ['name' => 'RKAlmanacModule', 'directory' => 'RK/AlmanacModule'], ['name' => 'Almanac']);
    }
    
    /**
     * Renames all permission rules stored for this app.
     */
    protected function renamePermissionsFor14()
    {
        $conn = $this->getConnection();
        $componentLength = strlen('Almanac') + 1;
    
        $conn->executeQuery("
            UPDATE group_perms
            SET component = CONCAT('RKAlmanacModule', SUBSTRING(component, $componentLength))
            WHERE component LIKE 'Almanac%';
        ");
    }
    
    /**
     * Renames all category registries stored for this app.
     */
    protected function renameCategoryRegistriesFor14()
    {
        $conn = $this->getConnection();
        $componentLength = strlen('Almanac') + 1;
    
        $conn->executeQuery("
            UPDATE categories_registry
            SET modname = CONCAT('RKAlmanacModule', SUBSTRING(modname, $componentLength))
            WHERE modname LIKE 'Almanac%';
        ");
    }
    
    /**
     * Renames all (existing) tables of this app.
     */
    protected function renameTablesFor14()
    {
        $conn = $this->getConnection();
    
        $oldPrefix = 'alma_';
        $oldPrefixLength = strlen($oldPrefix);
        $newPrefix = 'rk_alma_';
    
        $sm = $conn->getSchemaManager();
        $tables = $sm->listTables();
        foreach ($tables as $table) {
            $tableName = $table->getName();
            if (substr($tableName, 0, $oldPrefixLength) != $oldPrefix) {
                continue;
            }
    
            $newTableName = str_replace($oldPrefix, $newPrefix, $tableName);
    
            $conn->executeQuery("
                RENAME TABLE $tableName
                TO $newTableName;
            ");
        }
    }
    
    /**
     * Removes event handlers from database as they are now described by service definitions and managed by dependency injection.
     */
    protected function dropEventHandlersFromDatabase()
    {
        \EventUtil::unregisterPersistentModuleHandlers('Almanac');
    }
    
    /**
     * Updates the module name in the hook tables.
     */
    protected function updateHookNamesFor14()
    {
        $conn = $this->getConnection();
    
        $conn->update('hook_area', ['owner' => 'RKAlmanacModule'], ['owner' => 'Almanac']);
    
        $componentLength = strlen('subscriber.almanac') + 1;
        $conn->executeQuery("
            UPDATE hook_area
            SET areaname = CONCAT('subscriber.rkalmanacmodule', SUBSTRING(areaname, $componentLength))
            WHERE areaname LIKE 'subscriber.almanac%';
        ");
    
        $conn->update('hook_binding', ['sowner' => 'RKAlmanacModule'], ['sowner' => 'Almanac']);
    
        $conn->update('hook_runtime', ['sowner' => 'RKAlmanacModule'], ['sowner' => 'Almanac']);
    
        $componentLength = strlen('almanac') + 1;
        $conn->executeQuery("
            UPDATE hook_runtime
            SET eventname = CONCAT('rkalmanacmodule', SUBSTRING(eventname, $componentLength))
            WHERE eventname LIKE 'almanac%';
        ");
    
        $conn->update('hook_subscriber', ['owner' => 'RKAlmanacModule'], ['owner' => 'Almanac']);
    
        $componentLength = strlen('almanac') + 1;
        $conn->executeQuery("
            UPDATE hook_subscriber
            SET eventname = CONCAT('rkalmanacmodule', SUBSTRING(eventname, $componentLength))
            WHERE eventname LIKE 'almanac%';
        ");
    }
    
    /**
     * Updates the module name in the workflows table.
     */
    protected function updateWorkflowsFor14()
    {
        $conn = $this->getConnection();
        $conn->update('workflows', ['module' => 'RKAlmanacModule'], ['module' => 'Almanac']);
        $conn->update('workflows', ['obj_table' => 'DateEntity'], ['module' => 'RKAlmanacModule', 'obj_table' => 'date']);
    }
    
    /**
     * Returns connection to the database.
     *
     * @return Connection the current connection
     */
    protected function getConnection()
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
    
        return $entityManager->getConnection();
    }
    
    /**
     * Uninstall RKAlmanacModule.
     *
     * @return boolean True on success, false otherwise
     *
     * @throws RuntimeException Thrown if database tables or stored workflows can not be removed
     */
    public function uninstall()
    {
        $logger = $this->container->get('logger');
    
        try {
            $this->schemaTool->drop($this->listEntityClasses());
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->__('Doctrine Exception') . ': ' . $exception->getMessage());
            $logger->error('{app}: Could not remove the database tables during uninstallation. Error details: {errorMessage}.', ['app' => 'RKAlmanacModule', 'errorMessage' => $exception->getMessage()]);
    
            return false;
        }
    
        // remove all module vars
        $this->delVars();
    
        // remove category registry entries
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $registries = $this->container->get('zikula_categories_module.category_registry_repository')->findBy(['modname' => 'RKAlmanacModule']);
        foreach ($registries as $registry) {
            $entityManager->remove($registry);
        }
        $entityManager->flush();
    
        // remind user about upload folders not being deleted
        $uploadPath = $this->container->getParameter('datadir') . '/RKAlmanacModule/';
        $this->addFlash('status', $this->__f('The upload directories at "%path%" can be removed manually.', ['%path%' => $uploadPath]));
    
        // uninstallation successful
        return true;
    }
    
    /**
     * Build array with all entity classes for RKAlmanacModule.
     *
     * @return string[] List of class names
     */
    protected function listEntityClasses()
    {
        $classNames = [];
        $classNames[] = 'RK\AlmanacModule\Entity\DateEntity';
        $classNames[] = 'RK\AlmanacModule\Entity\DateCategoryEntity';
        $classNames[] = 'RK\AlmanacModule\Entity\HookAssignmentEntity';
    
        return $classNames;
    }
}
