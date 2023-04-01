import React, {useEffect, useRef, useState} from 'react'
import {useEnrichedClasses} from "./queries/Classes";
import {Button} from "primereact/button";
import {InputText} from 'primereact/inputtext';
import {EnrichedClass} from "./Models/Classes";
import {useDebounce} from 'usehooks-ts'
import {DataTable} from "primereact/datatable";
import {Column} from 'primereact/column';

interface ClassType {
    label: string,
    active: boolean,
    count: number
}

const loadCategories = (classes: EnrichedClass[]): ClassType[] => Array.from(
    classes.reduce((classTypes, _class) => {
        if (!classTypes.has(_class.category)) {
            classTypes.set(_class.category, {
                label: _class.category,
                active: true,
                count: 0
            });
        }
        classTypes.get(_class.category).count++
        return classTypes;
    }, new Map<string, ClassType>()).values()
)

const Classes = ({onSelect}: { onSelect: (name: string) => void }) => {
    const {isLoading, error, data: classes}: { data: EnrichedClass[] } = useEnrichedClasses();

    const [filteredClasses, setFilteredClasses] = useState<EnrichedClass[]>([]);
    const [classTypes, setClassTypes] = useState<ClassType[]>([]);
    const [activeClassTypes, setActiveClassTypes] = useState<string[]>([]);
    const [filter, setFilter] = useState<string>('');
    const [selectedClass, setSelectedClass] = useState<EnrichedClass>(null);
    const debouncedFilter = useDebounce<string>(filter, 100);
    const highlight = useRef<(text: string) => { __html: string }>();

    useEffect(() => {
        if (classes === undefined) {
            return;
        }
        if (classes.length === 0) {
            return;
        }
        setClassTypes(loadCategories(classes));
        setFilteredClasses(classes);
    }, [classes])

    useEffect(() => {
        if (classTypes == undefined) {
            return;
        }
        setActiveClassTypes([...classTypes.filter((type) => type.active).map((type) => type.label)]);
    }, [classTypes]);

    useEffect(() => {
        if (classes === undefined) {
            setFilteredClasses([]);
            return;
        }
        if (filter === '') {
            highlight.current = (text: string) => {
                let idx = 1;
                return {
                    __html: text
                }
            }
            setFilteredClasses(classes.filter((item) => activeClassTypes.indexOf(item.category) !== -1));
            return;
        }
        const filterRegularExpressionAsString =
            '(.*)' +
            (filter
                    .replace(/([a-z0-9](?=[A-Z]))/g, '$1 ')
                    .toLowerCase()
                    .replace(/\s+/g, ' ')
                    .replace(/\s/g, ' ')
                    .trim()
                    .split(' ')
                    .join('(.*)')
            ) +
            '(.*)';
        const filterRegularExpression = new RegExp(filterRegularExpressionAsString, 'i');
        highlight.current = (text: string) => {
            let idx = 1;
            return {
                __html: text.replace(
                    filterRegularExpression,
                    filterRegularExpressionAsString
                        .replaceAll('(.*)', (a, b) => `</strong>$${idx++}<strong>`))
                    .replaceAll('<strong></strong>', '')
            };
        }
        setFilteredClasses(classes.filter((item) => {
            return item.namespacedName.toLowerCase().match(filterRegularExpression) && activeClassTypes.indexOf(item.category) !== -1
        }));
    }, [debouncedFilter, classes, activeClassTypes])

    if (isLoading) return 'Loading';
    if (error) return 'Error ' + error;

    const toggleTypeInFilter = (type: string) => {
        setClassTypes(classTypes.map((displayedType) => {
            if (displayedType.label === type) {
                displayedType.active = !displayedType.active;
            }
            return displayedType;
        }));
    }

    const isTypeActive = (type: string) => {
        return classTypes.filter((displayedType) => displayedType.label === type)[0].active;
    };

    return (
        <>
            <span className="p-buttonset">
            {classTypes && classTypes.map((classType) => (
                <Button
                    key={classType.label}
                    size="small"
                    badge={'' + classType.count}
                    variant="contained"
                    severity={isTypeActive(classType.label) ? undefined : 'secondary'}
                    onClick={() => toggleTypeInFilter(classType.label)}
                >
                    {classType.label}
                </Button>
            ))}
            </span>
            <InputText value={filter} onChange={(e) => setFilter(e.target.value)}/>
            <DataTable<EnrichedClass[]>
                value={filteredClasses}
                tableStyle={{minWidth: '50rem'}}
                scrollable
                scrollHeight="calc(100vh - 180px)"
                selectionMode="single"
                selection={selectedClass}
                dataKey="namespacedName"
                onSelectionChange={(e) => {
                    setSelectedClass(e.value)
                    onSelect(e.value.namespacedName)
                }}
            >
                <Column body={((rowData) => <>
                    {filter === '' && rowData.namespacedName}
                    {highlight.current && <span dangerouslySetInnerHTML={highlight.current(rowData.namespacedName)}/>}
                </>)} header="FQCN"></Column>
                <Column field="category" header="Category"></Column>
            </DataTable>
        </>
    )
    return (
        <table className={"fixed_header"}>
            <thead>
            <tr>
                <td colSpan={2}>
                </td>
            </tr>
            <tr>
                <td width={200}>
                    Type
                </td>
                <td>
                    <InputText value={filter} onChange={(e) => setFilter(e.target.value)}/>
                </td>
            </tr>
            </thead>
            <tbody>
            {filteredClasses.map((method) => (
                <tr key={method.namespacedName}>
                    <td align="left">
                        <Button text="map" onClick={() => onSelect(method.namespacedName)}>Map</Button>
                    </td>
                    <td align="left">
                        {method.category}
                    </td>
                    <td
                        align="left"
                    >

                    </td>
                </tr>
            ))}
            </tbody>
        </table>
    );
}

export {
    Classes,
}
