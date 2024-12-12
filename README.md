# Suffle.Snapshot

A Neos CMS package to perform snapshot tests of fusion components

## Regression testing for fusion components

Inspired by Jest and its snapshot testing for React Components, this package helps to reduce unwanted side effects when changing existing Fusion Components. Especially in big projects it is often hard to keep an eye on  every usage of a component and which other component might be influenced. Snapshot testing reduces regression bugs by rendering Fusion Components and saving their outcome. If the code of a Fusion Component is changed, the snapshots are no longer in sync with the new outcome, which gives the developer two possibilities:

1. Adjust the code to keep old behaviour where needed
1. Update the old snapshots

Nevertheless, the developer needs to address the changed code somehow. The tests can also be run automatically as part of a CI-Build.

## Installation

Add the dependency to your project like this:

    composer require --dev suffle/snapshot

## Example configuration

The Snapshot Tests are configured as regular Flow settings.

````yaml
Suffle:
  Snapshot:
    snapshotSavePath: '%FLOW_PATH_DATA%Persistent/fusionSnapshots/'
    annotationKey: 'snapshot'
````

### snapshotSavePath

The snapshotSavePath defines the directory, where the snapshots are saved. If you use any Version Control Software like Git, make sure not to ignore this folder.

## annotationKey

The annotationKey can be set, if you want to use another name to annotate test cases. Since the syntax of the cases is the same used for the [Monocle Styleguide](https://github.com/sitegeist/Sitegeist.Monocle), you can use the styleguide annotation for the testing as well.

## Annotating Test Cases

As long as the annotation ist present, the component is tested in a pure version, which uses the basic props of the Fusion Component.

```
prototype(Vendor.Package:Components.Example) < prototype(Neos.Fusion:Component) {

    # Snapshot annotation
    # only if this annotation (or the another key in the settings) is
    # present the prototype is tested
    #

    @snapshot {

        # Default props for testing
        props {
            href = 'neos.io'
            classes = 'link-class'
        }

        #
        # Optional test cases which overload the default props
        #
        propSets {
            # This propSet still uses href and classes
            # from props
            'targetBlank' {
                target = '_blank'
            }

            # This propSet overwrites the classes but keeps the href
            'differentClass' {
                classes = 'other-link-class'
            }
        }
    }

    # basic fusion props
    href = ''
    classes = ''
    target = '_self'

    renderer = Neos.Fusion:Tag {
        tagName = 'a'
        attributes = {
            href = ${props.href}
            target = ${props.target}
            class = ${props.classes}
        }

        @if.hasHref = ${props.href}
    }
}
```

### Keep in mind
The snapshot tests are not using any database content or context variables other than the ones set directly in the tested component. You can't test any actual nodes or refer to context variables like `node`, `site` or `documentNode`.


## Taking Snapshots

You can take snapshots of components via the cli:

### Take snapshots of all components

If you want to take snapshots of your annotated Fusion Components, you can use the cli to take snapshots of all components:

```bash
./flow snapshot:takeall
```

This takes and **overwrites** snapshots of all components in all active site packages.

If there is no snapshot present for a component, the first snapshot will also be taken automatically during a test run.

### Take Snapshot of specific component

If you want to restrict the snapshots taken to a specific component, you can use the take-Command:

```bash
./flow snapshot:take Vendor.Package:Components.Example
```

## Testing components

Like with taking new snapshots, it is also possible to test all or only specific components. If you test new components or propSets without any snapshots available, the snapshots will be written on the first run.

### Test all components

As a standard test mechanism this tests all components from all site packages prepared for testing:

```bash
./flow snapshot:testall
```

### Test single component

Sometimes it is useful to test only single components, e.g. while developing:

```bash
./flow snapshot:test Vendor.Package:Components.Example
```

### Using interactive mode

By using the `--interactive` flag with a test command, the interactive mode is turned on. When a test of a component and its propSet fails, the diff of the rendered Fusion and the snapshots are shown and a prompt of what to do next, possibilities are:

| Input | Effect |
| --- | --- |
|y|Update the current snapshot|
|n|Do not update the snapshot, causing the test to fail|
|q|Immediately stop the testing|
|a|Update the current and all following snapshots that fail|
|d|Do not update this or any of the following snapshots that fail|

### Update all failing snapshots

It is also possible to automatically update all snapshots of failing tests. Just add the `--updateall` flag to a test command.

## List all items to test

If you need a list of all components that are ready for testing, you can use:

```bash
./flow snapshot:items
```

## Restricting command to a specific site package

All CLI Commands can be restricted to a specific site package. Just add `--packageName "sitePackageName"` as an option.
