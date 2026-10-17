import ProductCard from "@/components/ui/product-card";

export default function ProductCardDemo() {
  return (
    <div className="flex w-full items-center justify-center p-8">
      <div className="grid w-full max-w-2xl grid-cols-1 gap-6 sm:grid-cols-2">
        <ProductCard
          image="https://cdn.21st.dev/assets/mirror/98/98dcce0070228047422c5dc34b6fe4ef0f86a8db366615692cfa8b1e13e573a8.jpg"
          originalPrice={179}
          price={129}
          rating={4.5}
          title="Nike Air Max"
        />
        <ProductCard
          image="https://cdn.21st.dev/assets/mirror/73/736b3e09ec92f43fa5ae45c09aaf408bf53034721f2ae2c050871266033f1e07.jpg"
          price={89}
          rating={5}
          title="Luxury Perfume"
        />
      </div>
    </div>
  );
}
