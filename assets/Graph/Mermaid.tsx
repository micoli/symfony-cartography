import React, {useEffect, useState} from 'react'
import Mermaid from "../Component/Mermaid";
import {useEnrichedClasses} from "../queries/Classes";

const EnrichedClassesNetwork = () => {
    const {isLoading, error, data: _enrichedClasses, isFetching} = useEnrichedClasses();
    const [chart, setChart] = useState('')

    useEffect(() => {
        if (isLoading) {
            return;
        }
        if (_enrichedClasses === undefined) {
            return;
        }
        const declaredClasses = _enrichedClasses.map((enrichedClass) =>enrichedClass.namespacedName);
        const calls = [];
        _enrichedClasses.map((enrichedClass) => {
            Object.keys(enrichedClass.methods).map((methodName) => {
                enrichedClass.methods[methodName].calls.map((callMethod) => {
                    if(declaredClasses.indexOf(callMethod.to.namespacedName)===-1){
                        return;
                    }
                    calls.push({
                        source: callMethod.from.namespacedName.replace(/\\/g,'_'),
                        target: callMethod.to.namespacedName.replace(/\\/g,'_'),
                    })
                })
            })
        });
        const _chart = `classDiagram
            ${_enrichedClasses.map((enrichedClass) => `
                class ${enrichedClass.namespacedName.replace(/\\/g,'_')} {
                <<<${enrichedClass.category}>>>                    
                }
            `).join('\n')}        
            ${calls.map((call) => `${call.source} *-- ${call.target}`).join('\n')}        
        `;
        setChart(_chart);
    }, [_enrichedClasses]);

    if (isLoading) return "Loading...";

    if (error) return "An error has occurred: " + error.message;
    if (chart==='') return "generating";

    return <Mermaid chart={chart}/>;
}
const __EnrichedClassesNetwork = () => {
    const {isLoading, error, data: _enrichedClasses, isFetching} = useEnrichedClasses();
    const [elements, setElements] = useState([])
    useEffect(() => {
        if (isLoading) {
            return;
        }
        if (_enrichedClasses === undefined) {
            return;
        }
        const nodes = [];
        const edges = [];
        const declaredClasses = _enrichedClasses.map((enrichedClass) =>enrichedClass.namespacedName);
        _enrichedClasses.map((enrichedClass) => {
            nodes.push({
                data: {
                    id: enrichedClass.namespacedName,
                    label: enrichedClass.namespacedName
                }
            });

            Object.keys(enrichedClass.methods).map((methodName) => {
                const method = enrichedClass.methods[methodName];
                method.calls.map((callMethod) => {
                    if(declaredClasses.indexOf(callMethod.to.namespacedName)===-1){
                        console.log('eee');
                        return;
                    }
                    edges.push({
                        data: {
                            source: callMethod.from.namespacedName,
                            target: callMethod.to.namespacedName,
                        }
                    })
                })
            })
        })
        setElements({nodes, edges});
    }, [_enrichedClasses]);

    if (isLoading) return "Loading...";

    if (error) return "An error has occurred: " + error.message;

    if (elements.length===0) return "working";

    return <CytoscapeComponent elements={CytoscapeComponent.normalizeElements(elements)} style={{width: '400px', height: '300px'}}/>;
}

export {
    EnrichedClassesNetwork
}
