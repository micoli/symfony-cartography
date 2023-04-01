interface Argument {
    type: string,
    value: string
}

interface MethodName {
    namespacedName: string,
    name: string
}

interface MethodCall {
    from: MethodName,
    to: MethodName,
    arguments: Argument[],
    label?: string
}

interface Method {
    methodName: MethodName,
    calls: MethodCall[]
}

interface EnrichedClass {
    namespacedName: string,
    name: string,
    category: string,
    methods: Map<string, Method>
}

export {
    Argument,
    MethodName,
    MethodCall,
    Method,
    EnrichedClass,
}


