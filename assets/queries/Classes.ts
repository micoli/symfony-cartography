import axios from "axios";
import {useQuery} from "@tanstack/react-query";
import {EnrichedClass} from "../Models/Classes";

const useGraph = (className: string) => useQuery<string>({
    queryKey: ['repoGraph', className],
    queryFn: () =>
        axios
            .get('./graph', {
                params: {
                    className
                },
                responseType: 'text'
            })
            .then((res) => res.data)
});

const useEnrichedClasses = () => useQuery<EnrichedClass[]>({
    queryKey: ['repoEnrichedClasses'],
    queryFn: () =>
        axios
            .get('./enrichedClasses')
            .then((res) => res.data)
});
export {
    useGraph,
    useEnrichedClasses,
}
