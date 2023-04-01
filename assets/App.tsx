import React, {useState} from 'react'
import './App.css'
import './Component/Mermaid'
import {ReactQueryDevtools} from "@tanstack/react-query-devtools";
import {useGraph} from "./queries/Classes";
import SVG from 'react-inlinesvg';
import "primereact/resources/themes/lara-light-indigo/theme.css";
import "primereact/resources/primereact.min.css";
import "primeicons/primeicons.css";

import {Classes} from "./Classes";
import { TabView, TabPanel } from 'primereact/tabview';

const Graph = ({className}:{className:string}) => {
    const {isLoading, error, data: graph, isFetching} = useGraph(className);
    if (isLoading) return 'Loading graph';
    if (error) return 'Error ' + error;

    return <SVG src={`data:image/svg+xml;base64,${btoa(graph)}`} />
}

const App = () => {
    const [graphClass,setGraphClass] = useState<string>(null);
    const [activeIndex,setActiveIndex] = useState<number>(1);
    return (
        <div className="App">
            <TabView activeIndex={activeIndex} onTabChange={(e) => setActiveIndex(e.index)}>
                <TabPanel header="Classes" leftIcon="pi pi-align-justify mr-2">
                    <Classes onSelect={(className)=>{
                        setGraphClass(className);
                        setActiveIndex(1);
                    }} />
                </TabPanel>
                <TabPanel header={`Graph ${graphClass||'-'}`} leftIcon="pi pi-sitemap mr-2">
                    <div style={{overflow:'auto'}}>
                        <Graph key={graphClass} className={graphClass}/>
                    </div>
                </TabPanel>
            </TabView>
            <ReactQueryDevtools initialIsOpen={false}/>
        </div>
    )
}

export default App
