import React, { SFC } from 'react';

interface PropSetHeaderProps {
    className?: string
}

const PropSetHeader: SFC<PropSetHeaderProps> = ({className}) => {
    return <div className={className}>
        <h2 style={{marginTop: 0}}>PropSets</h2>
    </div>
}

export default PropSetHeader;