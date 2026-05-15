"use client";

import { useState, FormEvent, ChangeEvent, FocusEvent } from "react";
import Image from "next/image";

interface FormData {
  firstName: string;
  lastName: string;
  email: string;
  phoneNumber: string;
  country: string;
  dateOfBirth: string;
  agreedToTerms: boolean;
}

interface FormErrors {
  firstName?: string;
  lastName?: string;
  email?: string;
  agreedToTerms?: string;
}

const COUNTRIES = [
  "United States",
  "United Kingdom",
  "Canada",
  "Australia",
  "Germany",
  "France",
  "Spain",
  "Italy",
  "Netherlands",
  "Brazil",
  "Mexico",
  "Japan",
  "China",
  "India",
  "Israel",
  "South Africa",
];

const BASE_INPUT_CLASS =
  "w-full md:w-[335px] h-[48px] px-4 border border-[#686868] text-[13px] md:text-[15px] font-normal leading-[100%] tracking-[0%] text-black outline-none transition-colors";

const INITIAL_FORM: FormData = {
  firstName: "",
  lastName: "",
  email: "",
  phoneNumber: "",
  country: "",
  dateOfBirth: "",
  agreedToTerms: false,
};

function validate(data: FormData): FormErrors {
  const errors: FormErrors = {};
  if (!data.firstName.trim()) errors.firstName = "First name is required";
  if (!data.lastName.trim()) errors.lastName = "Last name is required";
  if (!data.email.trim()) {
    errors.email = "Email is required";
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    errors.email = "Please enter a valid email address";
  }
  if (!data.agreedToTerms) errors.agreedToTerms = "You must agree to the terms and conditions";
  return errors;
}

export default function Home() {
  const [formData, setFormData] = useState<FormData>(INITIAL_FORM);
  const [errors, setErrors] = useState<FormErrors>({});
  const [touched, setTouched] = useState<Record<string, boolean>>({});

  const inputClass = (field: keyof FormErrors) => {
    const hasError = errors[field] && touched[field];
    return `${BASE_INPUT_CLASS} ${hasError ? "border-red-500 focus:border-red-500" : "focus:border-[#4E99CD]"}`;
  };

  const handleChange = (e: ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target;
    const newValue =
      type === "checkbox" ? (e.target as HTMLInputElement).checked : value;

    const updated = { ...formData, [name]: newValue };

    setFormData(updated);

    if (touched[name]) {
      setErrors(validate(updated));
    }
  };

  const handleBlur = (e: FocusEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name } = e.target;
    setTouched((prev) => ({ ...prev, [name]: true }));
    setErrors(validate(formData));
  };

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    const allTouched = { firstName: true, lastName: true, email: true, agreedToTerms: true };
    setTouched(allTouched);
    const validationErrors = validate(formData);
    setErrors(validationErrors);
  };

  return (
    <div className="min-h-screen bg-[#EFF3F8] py-8 px-4 md:py-0 md:px-0">
      <div className="mx-auto w-full md:w-[974px]">
        <h1 className="text-center text-[21px] font-bold leading-[100%] tracking-[0%] text-[#B1BCCA] uppercase mt-[29px] md:mt-[79px] mb-[34px] md:mb-[55px] underline underline-offset-4 decoration-[#B1BCCA]">
          Submit your application
        </h1>

        <div className="bg-white p-6 md:p-[40px] md:w-[974px] h-[860px] md:h-[705px] overflow-visible md:overflow-hidden">
          <div className="mb-[40px]">
            <h2 className="text-[16px] md:text-[21px] font-bold leading-[100%] tracking-[0%] text-black mb-[18px]">
              Personal Information
            </h2>
            <p className="text-[14px] md:text-[19px] font-normal leading-[100%] tracking-[0%] text-[#686868]">
              Please fill in all mandatory fields
            </p>
          </div>

          <form onSubmit={handleSubmit} noValidate>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-[30px] md:w-[713px]">
              <div>
                <input
                  type="text"
                  name="firstName"
                  placeholder="*First Name"
                  value={formData.firstName}
                  onChange={handleChange}
                  onBlur={handleBlur}
                  className={inputClass("firstName")}
                />
                {errors.firstName && touched.firstName && (
                  <p className="mt-[4px] text-xs text-red-500">{errors.firstName}</p>
                )}
              </div>

              <div>
                <input
                  type="text"
                  name="lastName"
                  placeholder="*Last Name"
                  value={formData.lastName}
                  onChange={handleChange}
                  onBlur={handleBlur}
                  className={inputClass("lastName")}
                />
                {errors.lastName && touched.lastName && (
                  <p className="mt-[4px] text-xs text-red-500">{errors.lastName}</p>
                )}
              </div>

              <div>
                <input
                  type="email"
                  name="email"
                  placeholder="*Email"
                  value={formData.email}
                  onChange={handleChange}
                  onBlur={handleBlur}
                  className={inputClass("email")}
                />
                {errors.email && touched.email && (
                  <p className="mt-[4px] text-xs text-red-500">{errors.email}</p>
                )}
              </div>

              <div>
                <input
                  type="tel"
                  name="phoneNumber"
                  placeholder="Phone Number"
                  value={formData.phoneNumber}
                  onChange={handleChange}
                  onBlur={handleBlur}
                  className={`${BASE_INPUT_CLASS} focus:border-[#4E99CD]`}
                />
              </div>

              <div>
                <select
                  name="country"
                  value={formData.country}
                  onChange={handleChange}
                  onBlur={handleBlur}
                  className={`${BASE_INPUT_CLASS} focus:border-[#4E99CD] bg-white appearance-none`}
                  style={{
                    backgroundImage: `url("/arrow-down.svg")`,
                    backgroundRepeat: "no-repeat",
                    backgroundPosition: "right 16px center",
                  }}
                >
                  <option value="" disabled>Choose Country</option>
                  {COUNTRIES.map((country) => (
                    <option key={country} value={country}>{country}</option>
                  ))}
                </select>
              </div>

              <div className="relative md:w-[335px]">
                <input
                  type="date"
                  name="dateOfBirth"
                  value={formData.dateOfBirth}
                  onChange={handleChange}
                  onBlur={handleBlur}
                  className={`${BASE_INPUT_CLASS} focus:border-[#4E99CD] bg-transparent relative z-10`}
                  style={{ color: formData.dateOfBirth ? "inherit" : "transparent" }}
                />
                {!formData.dateOfBirth && (
                  <span className="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[13px] md:text-[15px] font-normal leading-[100%] tracking-[0%] text-[#686868] z-0">
                    Date of Birth
                  </span>
                )}
                <div className="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 z-20">
                  <Image src="/calendar.svg" alt="Calendar" width={18} height={18} />
                </div>
              </div>
            </div>

            <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
              <div className="flex-1 min-w-0">
                <div className="hidden md:block w-[713px] border-t border-gray-200 mt-[44px]" />

                <div className="mt-[40px] mb-[30px]">
                  <div className="flex items-start gap-3">
                    <div className="relative mt-1">
                      <input
                        type="checkbox"
                        name="agreedToTerms"
                        id="agreedToTerms"
                        checked={formData.agreedToTerms}
                        onChange={handleChange}
                        onBlur={handleBlur}
                        className="peer sr-only"
                      />
                      <label
                        htmlFor="agreedToTerms"
                        className="block w-4 h-4 border border-[#686868] cursor-pointer"
                      >
                        {formData.agreedToTerms && (
                          <Image src="/check.svg" alt="Checked" width={16} height={16} style={{ width: "auto", height: "auto" }} />
                        )}
                      </label>
                    </div>
                    <label
                      htmlFor="agreedToTerms"
                      className="text-[13px] md:text-[17px] font-normal leading-[168%] tracking-[0%] text-black cursor-pointer"
                    >
                      I have read and agree to the{" "}
                      <a href="#" className="text-[13px] md:text-[17px] font-bold leading-[168%] tracking-[0%] text-black underline underline-offset-2 decoration-black">
                        Terms and Conditions
                      </a>{" "}
                      and the{" "}
                      <a href="#" className="text-[13px] md:text-[17px] font-bold leading-[168%] tracking-[0%] text-black underline underline-offset-2 decoration-black">
                        Privacy Policy
                      </a>
                    </label>
                  </div>
                  {errors.agreedToTerms && touched.agreedToTerms && (
                    <p className="mt-[4px] text-xs text-red-500 ml-7">{errors.agreedToTerms}</p>
                  )}
                </div>

                <div className="flex justify-center md:justify-start">
                  <button
                    type="submit"
                    className="w-[173px] h-[52px] flex items-center justify-center bg-[#4E99CD] text-white text-[17px] font-bold leading-[168%] tracking-[0%] uppercase hover:bg-[#3d8abf] transition-colors focus:outline-none focus:ring-2 focus:ring-[#4E99CD] focus:ring-offset-2"
                  >
                    Submit
                    <svg className="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </div>
              </div>

              <div className="flex justify-center md:block md:w-[301px] md:mt-[60px] md:-ml-[40px]">
                <Image
                  src="/image.png"
                  alt="Application illustration"
                  width={301}
                  height={280}
                  className="w-[215.84px] h-[196.26px] md:w-[301px] md:h-[280px]"
                  priority
                />
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
