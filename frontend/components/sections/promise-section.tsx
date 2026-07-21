"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { Icon } from "@/components/common/icon";

const promiseItems = [
  {
    icon: "Award",
    title: "Outstanding Cleaning Performance",
    description: "Powerful cleaning that gets the job done.",
  },
  {
    icon: "ShieldCheck",
    title: "Fabric Protection",
    description: "Gentle on fabrics, tough on dirt.",
  },
  {
    icon: "BadgeCheck",
    title: "Consistent Quality",
    description: "Reliable performance in every bottle.",
  },
  {
    icon: "Users",
    title: "Professional Results",
    description: "Designed for professional and everyday excellence.",
  },
];

const gradients = [
  "from-green-500 to-green-600 shadow-green-500/25",
  "from-[#0d3b66] to-[#1565c0] shadow-blue-500/25",
  "from-amber-500 to-amber-600 shadow-amber-500/25",
  "from-violet-500 to-violet-600 shadow-violet-500/25",
];

export function PromiseSection() {
  return (
    <section id="promise" className="py-20 lg:py-24 bg-white">
      <Container>
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {promiseItems.map((item, index) => (
            <motion.div
              key={item.title}
              initial={{ opacity: 0, y: 40 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-100px" }}
              transition={{ duration: 0.6, delay: index * 0.1 }}
              className="flex items-start gap-4 p-6 lg:p-7 rounded-[20px] bg-white border border-[#e2e8f0] shadow-sm hover:-translate-y-1.5 hover:shadow-md hover:border-[#7db8ec]/50 transition-all"
            >
              <div
                className={`w-14 h-14 lg:w-16 lg:h-16 rounded-full flex-shrink-0 flex items-center justify-center text-white bg-gradient-to-br ${gradients[index]} shadow-lg`}
              >
                <Icon name={item.icon} className="w-6 h-6 lg:w-7 lg:h-7" />
              </div>
              <div>
                <h3 className="text-base lg:text-lg font-bold text-[#0a1628] mb-1 leading-tight">
                  {item.title}
                </h3>
                <p className="text-sm text-[#64748b] leading-relaxed">{item.description}</p>
              </div>
            </motion.div>
          ))}
        </div>
      </Container>
    </section>
  );
}
