import React, { Component } from 'react';
import { createRoot } from "react-dom/client";

import { enGB, fr } from 'date-fns/locale';
import { parse } from 'date-fns';
import DatePicker from "react-datepicker";

import "react-datepicker/dist/react-datepicker.css";

export default class DayPicker extends Component {
    constructor (props) {
        super(props)

        let data = JSON.parse(this.props.data);

        this.format = data.format || 'dd/MM/yyyy';
        this.inputName = data.name || 'date';

        let parsedDate = this.parseDate(data.default, this.format);

        // Function date-fns.parse() will return an "Invalid Date" object if the
        // date is not valid.
        // It is still a Date object, we can detect it with isNaN() function.
        if (isNaN(parsedDate)) {
            this.invalidDateMessage = data.invalid_date_msg;
            parsedDate = new Date();
        }
        this.state = {
            value: parsedDate,
            locale: this.defineLocale(data.locale || "fr")
        };
    }

    defineLocale(locale) {
        switch (locale) {
            case 'en':
                return enGB;
            default:
                return fr;
        }
    }

    parseDate(date, format) {
        if (!date) return null;

        try {
            return parse(
                date.trim(),
                format.trim(),
                new Date()
            );
        } catch (err) {
            console.log(err);
            return null;
        }
    }

    handleDateChange(date) {
        this.setState(
            {
                value: date
            }
        );
    }

    render() {

        const invalidMessageComponent = (
            <div className="form-text text-danger">
                { this.invalidDateMessage }
            </div>
        );

        return (
            <div>
                <DatePicker
                    locale={this.state.locale}
                    selected={this.state.value}
                    onChange={(date) => this.handleDateChange(date)}
                    dateFormat={this.format}
                    className="form-control"
                    name={this.inputName}
                />
                { this.invalidDateMessage && invalidMessageComponent}
            </div>
        )
    }
}

const elementId = 'rct-date-picker';
if (document.getElementById(elementId)) {
    const root = createRoot(document.getElementById(elementId));

    let data = document.getElementById(elementId).getAttribute('data');
    root.render(<DayPicker data={ data } />);
}
