import useBoundingBox from '@/hooks/useBoundingBox';
import { useOnClickOutside } from '@/hooks/useOnClickOutside';
import { AnimatePresence, motion } from 'framer-motion';
import React, { CSSProperties, Fragment, useRef, useState } from 'react';
import ReactDOM from 'react-dom';
import styles from './SelectBox.scss';
import { BsChevronDown } from 'react-icons/bs';
import Dropdown, { IDropdownProps } from '../Dropdown/Dropdown';

export interface IOption<T = any> {
    value: T;
    text: string;
    isSelected?: boolean;
}

export interface SelectboxProps<T = any> extends IDropdownProps {
    multiselect?: boolean;
    initialOptions?: IOption<T>[];
    onChange?: (selectedOptions: IOption<T>[]) => void;
    searchBar?: {
        getOptions: (searchString: string) => IOption<T>[] | Promise<IOption<T>[]>
        debounceTimeMs?: number;
    }
}

const transition = { duration: 0.25, type: 'tween', ease: [0.45, 0, 0.55, 1] };

const itemsContainerAnimation = {
    initial: { transform: 'translateY(-15px)', opacity: 0 },
    animate: { transform: 'translateY(0px)', opacity: 1 },
    exit: { transform: 'translateY(15px)', opacity: 0 },
    transition
};

function Selectbox<T = any>(props: SelectboxProps<T>) {
    const {
        multiselect = false,
        initialOptions = [],
        onChange,
        searchBar,
        ...dropdownProps
    } = props;

    const [options, setOptions] = useState<IOption<T>[]>(initialOptions);

    if (multiselect) {

    }

    return (
        <Dropdown {...dropdownProps}>
            {options.map((op, i) => {
                return (
                    <li key={i}>{op.text}</li>
                );
            })}
        </Dropdown>
    );
}

export default Selectbox;